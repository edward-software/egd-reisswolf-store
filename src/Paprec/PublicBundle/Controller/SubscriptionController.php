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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $quoteRequest->setLocale($locale);
            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setFrequencyTimes($cart->getFrequencyTimes());
            $quoteRequest->setFrequencyInterval($cart->getFrequencyInterval());
            $quoteRequest->setNumber($quoteRequestManager->generateNumber($quoteRequest));

            $regionName = 'CH';
            if (!$quoteRequest->getIsMultisite() && $quoteRequest->getPostalCode()) {
                switch (strtolower($quoteRequest->getPostalCode()->getRegion()->getName())) {
                    case 'basel':
                        $regionName = 'BS';
                        break;
                    case 'geneve':
                        $regionName = 'GE';
                        break;
                    case 'zurich':
                    case 'zuerich':
                        $regionName = 'ZH';
                        break;
                    case 'luzern':
                        $regionName = 'LU';
                        break;
                }
            }


            $reference = strtoupper($regionName) . $quoteRequest->getDateCreation()->format('ymd');
            $reference .= '-' . str_pad($quoteRequestManager->getCountByReference($reference), 2, '0', STR_PAD_LEFT);
            $quoteRequest->setReference($reference);


            if ($quoteRequest->getIsMultisite()) {
                // TODO : Ajouter un commercial par défaut si pas de code postal saisi car Multisite
                $quoteRequest->setUserInCharge(null);
            } else {
                $quoteRequest->setUserInCharge($userManager->getUserInChargeByPostalCode($quoteRequest->getPostalCode()));
            }

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
            $sendConfirmEmail = $quoteRequestManager->sendConfirmRequestEmail($quoteRequest, $locale);
            $sendNewRequestEmail = $quoteRequestManager->sendNewRequestEmail($quoteRequest,
                $quoteRequest->getUserInCharge()->getLang());


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
//

    /**
     * @Route("/{locale}/contract/{quoteId}", name="paprec_public_contract_confirm_email")
     */
    public function showContract(Request $request, $quoteId, $locale)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $productManager = $this->get('paprec_catalog.product_manager');
        $products = $productManager->getAvailableProducts();

        $quoteRequest = $quoteRequestManager->get($quoteId);
        return $this->render('@PaprecCommercial/QuoteRequest/PDF/geneve/printQuoteContract.html.twig', array(
            'quoteRequest' => $quoteRequest,
            'date' => new \DateTime(),
            'products' => $products

        ));
    }
//

    /**
     * @Route("/{locale}/offer/{quoteId}", name="paprec_public_offer_confirm_email")
     */
    public function showOffer(Request $request, $quoteId, $locale)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequest = $quoteRequestManager->get($quoteId);
        $productManager = $this->container->get('paprec_catalog.product_manager');
        $products = $productManager->getAvailableProducts();
        return $this->render('@PaprecCommercial/QuoteRequest/PDF/geneve/printQuoteOffer.html.twig', array(
            'quoteRequest' => $quoteRequest,
            'products' => $products,
            'date' => new \DateTime(),
            'locale' => $locale,
            'tmpLockProg' => $this->getParameter('tmp_lock_prog')
        ));
    }
//

    /**
     * @Route("/{locale}/pdf/contract/{quoteId}", name="paprec_public_confirm_pdf_show_contract")
     */
    public function showContractPDF(Request $request, $quoteId, $locale)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequest = $quoteRequestManager->get($quoteId);
        $filename = $quoteRequestManager->generatePDF($quoteRequest, $locale);
        return $this->render('@PaprecCommercial/QuoteRequest/showPDF.html.twig', array(
            'filename' => $filename
        ));
    }

    /**
     * @Route("/{locale}/email/contract/{quoteId}", name="paprec_public_confirm_email_show_contract")
     */
    public function showEmail(Request $request, $quoteId, $locale)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequest = $quoteRequestManager->get($quoteId);
        return $this->render('@PaprecCommercial/QuoteRequest/emails/generatedQuoteEmail.html.twig', array(
            'quoteRequest' => $quoteRequest,
            'locale' => $locale
        ));
    }
}
