<?php

namespace Paprec\PublicBundle\Controller;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Form\QuoteRequestPublicType;
use Paprec\CommercialBundle\Form\QuoteRequestSignatoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends Controller
{

    /**
     * @Route("/", name="paprec_public_devis_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndex0Action(Request $request)
    {
        return $this->redirectToRoute('paprec_public_catalog_index', array('locale' => 'fr'));

    }

    /**
     * @Route("/{locale}", name="paprec_public_devis_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndexAction(Request $request, $locale)
    {
        return $this->redirectToRoute('paprec_public_catalog_index', array('locale' => $locale));

    }


    /**
     * @Route("/{locale}/step0/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_catalog_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function catalogAction(Request $request, $locale, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->get('paprec_catalog.product_manager');
        $otherNeedsManager = $this->get('paprec_catalog.other_need_manager');

        if (!$cartUuid) {
            $cart = $cartManager->create(90);
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('paprec_public_catalog_index', array(
                'locale' => $locale,
                'cartUuid' => $cart->getId()
            ));
        } else {
            $cart = $cartManager->get($cartUuid);

            $products = $productManager->getAvailableProducts();

            $otherNeeds = $otherNeedsManager->getByLocale($locale);

        }

        return $this->render('@PaprecPublic/Common/catalog.html.twig', array(
            'locale' => $locale,
            'cart' => $cart,
            'products' => $products,
            'otherNeeds' => $otherNeeds
        ));
    }

    /**
     * @Route("/{locale}/step1/{cartUuid}",  name="paprec_public_contact_index")
     * @param Request $request
     * @param $locale
     * @param $cartUuid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function contactDetailAction(Request $request, $locale, $cartUuid)
    {

        $cartManager = $this->get('paprec.cart_manager');
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $userManager = $this->get('paprec.user_manager');


        $cart = $cartManager->get($cartUuid);

        $access = array();
        foreach ($this->getParameter('paprec_quote_access') as $a) {
            $access[$a] = $a;
        }

        $staff = array();
        foreach ($this->getParameter('paprec_quote_staff') as $s) {
            $staff[$s] = $s;
        }

        $quoteRequest = $quoteRequestManager->add(false);

        $form = $this->createForm(QuoteRequestPublicType::class, $quoteRequest, array(
            'access' => $access,
            'staff' => $staff,
            'locale' => $locale
        ));

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid() && $this->captchaVerify($request->get('g-recaptcha-response'))) {
            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('CREATED');
            $quoteRequest->setLocale($locale);
            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setFrequencyTimes($cart->getFrequencyTimes());
            $quoteRequest->setFrequencyInterval($cart->getFrequencyInterval());
            $quoteRequest->setNumber($quoteRequestManager->generateNumber($quoteRequest));

            if ($cart->getOtherNeeds() && count($cart->getOtherNeeds())) {
                foreach ($cart->getOtherNeeds() as $otherNeed) {
                    if (strtolower($otherNeed->getLanguage()) === strtolower($locale)) {
                        $quoteRequest->addOtherNeed($otherNeed);
                        $otherNeed->addQuoteRequest($quoteRequest);
                    }
                }
            }

            $reference = $quoteRequestManager->generateReference($quoteRequest);
            $quoteRequest->setReference($reference);


            $quoteRequest->setUserInCharge($userManager->getUserInChargeByPostalCode($quoteRequest->getPostalCode()));


            $em = $this->getDoctrine()->getManager();
            $em->persist($quoteRequest);

            // On récupère tous les produits ajoutés au Cart
            if ($cart->getContent() !== null) {
                foreach ($cart->getContent() as $item) {
                    $quoteRequestManager->addLineFromCart($quoteRequest, $item['pId'], $item['qtty'], false);
                }
            }
            $em->flush();

            /**
             * On envoie le mail de confirmation à l'utilisateur
             */
            $sendConfirmEmail = $quoteRequestManager->sendConfirmRequestEmail($quoteRequest);
            $sendNewRequestEmail = $quoteRequestManager->sendNewRequestEmail($quoteRequest);


            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_confirm_index', array(
                    'locale' => $locale,
                    'cartUuid' => $cart->getId(),
                    'quoteRequestId' => $quoteRequest->getId()
                ));
            }
            exit;
        }


        return $this->render('@PaprecPublic/Common/contact.html.twig', array(
            'locale' => $locale,
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * A partir du token reCaptcha récupéré dans le formulaire
     * On fait une requête vers google pour vérifier la validité du Captcha
     *
     * @param $recaptchaToken
     * @return mixed
     */
    private function captchaVerify($recaptchaToken)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret" => $this->getParameter('recaptcha_secret_key'),
            "response" => $recaptchaToken
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }

    /**
     * @Route("/postalCode/autocomplete", name="paprec_public_postalCode_autocomplete")
     * @throws \Exception
     */
    public function autocompleteAction(Request $request)
    {
        $codes = array();
        $code = trim(strip_tags($request->get('term')));

        $postalCodeManager = $this->get('paprec_catalog.postal_code_manager');

        $entities = $postalCodeManager->getActivesFromCode($code);

        foreach ($entities as $entity) {
            $codes[] = $entity->getCode();
        }

        $response = new JsonResponse();
        $response->setData($codes);

        return $response;
    }

    /**
     * @Route("/{locale}/step2/{cartUuid}/{quoteRequestId}", name="paprec_public_confirm_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function confirmAction(Request $request, $locale, $cartUuid, $quoteRequestId)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $quoteRequest = $quoteRequestManager->get($quoteRequestId);

        return $this->render('@PaprecPublic/Common/confirm.html.twig', array(
            'locale' => $locale,
            'quoteRequest' => $quoteRequest,
            'cart' => $cart,
        ));
    }


    /**
     * @Route("/{locale}/addContent/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_catalog_addContent", condition="request.isXmlHttpRequest()")
     */
    public function addContentAction(Request $request, $locale, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        $productId = $request->get('productId');

        $quantity = $request->get('quantity');

        try {
            $product = $productManager->get($productId);
            $cart = $cartManager->addContent($cartUuid, $product, $quantity);


            return $this->render('@PaprecPublic/Common/partials/quoteLine.html.twig', array(
                'locale' => $locale,
                'product' => $product,
                'quantity' => $quantity
            ));

        } catch (\Exception $e) {
            return new JsonResponse(null, 400);
        }
    }

    /**
     * @Route("/{locale}/addFrequency/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_catalog_addFrequency", condition="request.isXmlHttpRequest()")
     */
    public function addFrequencyAction(Request $request, $locale, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $frequency = $request->get('frequency');
        $frequencyTimes = $request->get('frequency_times');
        $frequencyInterval = $request->get('frequency_interval');

        try {
            $cartManager->addFrequency($cartUuid, $frequency, $frequencyTimes, $frequencyInterval);
            $content = json_encode(array('message' => 'frequency_added'));
            return new JsonResponse($content, 204);

        } catch (\Exception $e) {
            return new JsonResponse(array('error' => $e->getMessage()), 400);
        }
    }

    /**
     * Augmente la quantité d'un produit dans le panier de 1
     * L'ajoute au panier si produit non présent
     *
     * @Route("/{locale}/addOneContent/{cartUuid}/{productId}", name="paprec_public_catalog_addOneContent", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function addOneProductAction(Request $request, $locale, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        try {
            $product = $productManager->get($productId);
            // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
            $qtty = $cartManager->addOneProduct($cartUuid, $productId);


            return $this->render('@PaprecPublic/Common/partials/quoteLine.html.twig', array(
                'locale' => $locale,
                'product' => $product,
                'quantity' => $qtty
            ));

        } catch (\Exception $e) {
            return new JsonResponse(null, 400);
        }
    }

    /**
     * Diminue la quantité d'un produit dans le panier de 1
     * Le supprime du panier si quantité = 0
     *
     * @Route("/{locale}/removeOneContent/{cartUuid}/{productId}", name="paprec_public_catalog_removeOneContent", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function removeOneProductAction(Request $request, $locale, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        try {
            $product = $productManager->get($productId);
            // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
            $qtty = $cartManager->removeOneProduct($cartUuid, $productId);

            if ($qtty > 0) {
                return $this->render('@PaprecPublic/Common/partials/quoteLine.html.twig', array(
                    'locale' => $locale,
                    'product' => $product,
                    'quantity' => $qtty
                ));
            } else {
                return new JsonResponse(null, 200);
            }


        } catch (\Exception $e) {
            return new JsonResponse(null, 400);
        }
    }


    /**
     * Ajout ou suppression d'un OtherNeed au Cart
     *
     * @Route("/{locale}/addRemoveOtherNeed/{cartUuid}/{otherNeedId}", name="paprec_public_catalog_addRemoveOtherNeed", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function addOrRemoveOtherNeedAction(Request $request, $locale, $cartUuid, $otherNeedId)
    {
        try {
            $cartManager = $this->get('paprec.cart_manager');
            $otherNeedManager = $this->container->get('paprec_catalog.other_need_manager');


            $otherNeed = $otherNeedManager->get($otherNeedId);

            // On ajoute ou supprime l'OtherNeed au cart
            $cartManager->addOrRemoveOtherNeed($cartUuid, $otherNeed);

            return new JsonResponse(null, 200);


        } catch (\Exception $e) {
            return new JsonResponse(null, 400);
        }
    }


    /**
     * @Route("/{locale}/signatory/{quoteRequestId}/{token}",  name="paprec_public_signatory_index")
     * @param Request $request
     * @param $locale
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function signatoryAction(Request $request, $locale, $quoteRequestId, $token)
    {
        try {
            $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');

            $quoteRequest = $quoteRequestManager->get($quoteRequestId);

            $quoteRequestManager->getActiveByIdAndToken($quoteRequestId, $token);

            $form = $this->createForm(QuoteRequestSignatoryType::class, $quoteRequest, array(
                'locale' => $locale
            ));

            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $quoteRequest = $form->getData();
                $quoteRequest->setQuoteStatus('CREATED');


                $em = $this->getDoctrine()->getManager();
                $em->persist($quoteRequest);

                $em->flush();

                /**
                 * On envoie le mail de confirmation à l'utilisateur
                 */
                $sendConfirmEmail = $quoteRequestManager->sendGeneratedContractEmail($quoteRequest);
                $sendNewRequestEmail = $quoteRequestManager->sendNewContractEmail($quoteRequest);


                if ($sendConfirmEmail && $sendNewRequestEmail) {
                    return $this->redirectToRoute('paprec_public_signatory_confirm_index', array(
                        'locale' => $locale,
                        'quoteRequestId' => $quoteRequest->getId(),
                        'token' => $token
                    ));
                }
                exit;
            }


            return $this->render('@PaprecPublic/Common/signatory.html.twig', array(
                'locale' => $locale,
                'form' => $form->createView()
            ));
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Not found');
        }
    }

    /**
     * @Route("/{locale}/signatory/confirm/{quoteRequestId}/{token}",  name="paprec_public_signatory_confirm_index")
     * @param Request $request
     * @param $locale
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function signatoryConfirmAction(Request $request, $locale, $quoteRequestId, $token)
    {
        try {
            $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');

            $quoteRequest = $quoteRequestManager->get($quoteRequestId);

            $quoteRequestManager->getActiveByIdAndToken($quoteRequestId, $token);

            return $this->render('@PaprecPublic/Common/signatory-confirm.html.twig', array(
                'locale' => $locale,
                'quoteRequest' => $quoteRequest
            ));

        } catch (\Exception $e) {
            throw $this->createNotFoundException('Not found');
        }
    }

}
