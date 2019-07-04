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
     * @Route("/", name="paprec_public_devis_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndexAction()
    {
        return $this->redirectToRoute('paprec_public_catalog_index');
    }


    /**
     * @Route("/step0/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_catalog_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function catalogAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->get('paprec_catalog.product_manager');

        if (!$cartUuid) {
            $cart = $cartManager->create(90);
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('paprec_public_catalog_index', array(
                'cartUuid' => $cart->getId()
            ));
        } else {
            $cart = $cartManager->get($cartUuid);

            $products = $productManager->getAvailableProducts();

        }

        return $this->render('@PaprecPublic/Common/catalog.html.twig', array(
            'lang' => 'FR',
            'cart' => $cart,
            'products' => $products
        ));
    }

    /**
     * @Route("/step1/{cartUuid}",  name="paprec_public_contact_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function contactDetailAction(Request $request, $cartUuid)
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($quoteRequest);
            $em->flush();

            // On récupère tous les produits ajoutés au Cart
            if ($cart->getContent() !== null) {
                foreach ($cart->getContent() as $item) {
                    $quoteRequestManager->addLineFromCart($quoteRequest, $item['pId'], $item['qtty']);
                }
            }

            return $this->redirectToRoute('paprec_public_confirm_index', array(
                'cartUuid' => $cart->getId(),
                'quoteRequestId' => $quoteRequest->getId()
            ));
        }


        return $this->render('@PaprecPublic/Common/contact.html.twig', array(
            'lang' => 'FR',
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/step2/{cartUuid}/{quoteRequestId}", name="paprec_public_confirm_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function confirmAction(Request $request, $cartUuid, $quoteRequestId)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $quoteRequest = $quoteRequestManager->get($quoteRequestId);

        return $this->render('@PaprecPublic/Common/confirm.html.twig', array(
            'lang' => 'FR',
            'quoteRequest' => $quoteRequest,
            'cart' => $cart,
        ));
    }


    /**
     * @Route("/addContent/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_catalog_addContent", condition="request.isXmlHttpRequest()")
     */
    public function addContentAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        $productId = $request->get('productId');

        $quantity = $request->get('quantity');

        try {
            $product = $productManager->get($productId);
            $cart = $cartManager->addContent($cartUuid, $product, $quantity);


            return $this->render('@PaprecPublic/Common/partials/quoteLine.html.twig', array(
                'lang' => 'FR',
                'product' => $product,
                'quantity' => $quantity
            ));

        } catch (\Exception $e) {
            return new JsonResponse(null, 400);
        }


    }
}
