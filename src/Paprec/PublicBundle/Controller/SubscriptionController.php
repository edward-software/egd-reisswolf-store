<?php

namespace Paprec\PublicBundle\Controller;


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

        $cart = $cartManager->get($cartUuid);

        $quoteRequest = new QuoteRequest();

        $form = $this->createForm(QuoteRequestPublicType::class, $quoteRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('CREATED');

            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setFrequencyTimes($cart->getFrequencyTimes());
            $quoteRequest->setFrequencyInterval($cart->getFrequencyInterval());

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
            $sendNewRequestEmail = $quoteRequestManager->sendnewRequestEmail($quoteRequest, $locale);

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
     * @Route("/{locale}/confirmEmail", name="paprec_public_confirm_confirm_email")
     */
    public function showConfirmEmail(Request $request, $locale)
    {
        return $this->render('@PaprecCommercial/QuoteRequest/emails/confirmQuoteEmail.html.twig');
    }
}
