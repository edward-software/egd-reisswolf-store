<?php

namespace Paprec\PublicBundle\Controller;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Form\QuoteRequestPublicType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends Controller
{

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

        }

        return $this->render('@PaprecPublic/Common/catalog.html.twig', array(
            'locale' => $locale,
            'cart' => $cart,
            'products' => $products
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

        $quoteRequest = new QuoteRequest();

        $form = $this->createForm(QuoteRequestPublicType::class, $quoteRequest, array(
            'access' => $access,
            'staff' => $staff,
            'locale' => $locale
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->captchaVerify($request->get('g-recaptcha-response'))) {
            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('CREATED');

            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setFrequencyTimes($cart->getFrequencyTimes());
            $quoteRequest->setFrequencyInterval($cart->getFrequencyInterval());

            if ($quoteRequest->getIsMultisite()) {
                // TODO : Ajouter un commercial par défaut si pas de code postal saisi car Multisite
            } else {
                $quoteRequest->setUserInCharge($userManager->getUserInChargeByPostalCode($quoteRequest->getPostalCode()));
            }
            $quoteRequest->setLocale($locale);

            $em = $this->getDoctrine()->getManager();
            $em->persist($quoteRequest);
            $em->flush();

            // On récupère tous les produits ajoutés au Cart
            if ($cart->getContent() !== null) {
                foreach ($cart->getContent() as $item) {
                    $quoteRequestManager->addLineFromCart($quoteRequest, $item['pId'], $item['qtty']);
                }
            }

            $sendConfirmEmail = $quoteRequestManager->sendConfirmRequestEmail($quoteRequest, $locale);
            $sendNewRequestEmail = $quoteRequestManager->sendNewRequestEmail($quoteRequest, $locale);

            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_confirm_index', array(
                    'locale' => $locale,
                    'cartUuid' => $cart->getId(),
                    'quoteRequestId' => $quoteRequest->getId()
                ));
            }
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret" => $this->getParameter('recaptcha_secret_key'), "response" => $recaptchaToken));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
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
     * @Route("/{locale}/confirmEmail", name="paprec_public_confirm_confirm_email")
     */
    public function showConfirmEmail(Request $request, $locale)
    {
        return $this->render('@PaprecCommercial/QuoteRequest/emails/confirmQuoteEmail.html.twig');
    }
}
