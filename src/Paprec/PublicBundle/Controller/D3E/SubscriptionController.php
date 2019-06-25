<?php

namespace Paprec\PublicBundle\Controller\D3E;

use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderDeliveryType;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderShortType;
use Paprec\CommercialBundle\Form\ProductD3EQuote\ProductD3EQuoteShortType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{
    /**
     * @Route("/d3e/step0/{cartUuid}", name="paprec_public_corp_d3e_subscription_step0")
     * @throws \Exception
     */
    public function step0Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // Pour alimenter le "select" des types de déchets
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }

        return $this->render('@PaprecPublic/D3E/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,

        ));
    }

    /**
     * @Route("/d3e/setOrder/{cartUuid}", name="paprec_public_corp_d3e_subscription_setOrder")
     * @throws \Exception
     */
    public function setOrderAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('order');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_d3e_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/d3e/setQuote/{cartUuid}", name="paprec_public_corp_d3e_subscription_setQuote")
     * @throws \Exception
     */
    public function setQuoteAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('quote');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_d3e_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Etape "Mon besoin", choix des produits et ajout au Cart
     *
     * On passe le $type en paramère qui correspond à 'order' (commande) ou 'quote'(devis)
     * @Route("/d3e/step1/{cartUuid}", name="paprec_public_corp_d3e_subscription_step1")
     * @throws \Exception
     */
    public function step1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $typeManager = $this->get('paprec_catalog.type_manager');


        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        // On récupère les produits D3E pour afficher le choix des produits (ou prestations)
        $products = $productD3EManager->findAvailables(array(
            'isPackage' => false,
            'postalCode' => $cart->getPostalCode()
        ));

        // Pour alimenter le "select" des types de déchets
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }

        /*
         * Si il y a des displayedProducts, il faut récupérer leurs types pour les afficher
         */
        $productsTypes = array();

        if ($cart->getDisplayedProducts() && count($cart->getDisplayedProducts())) {
            foreach ($cart->getDisplayedProducts() as $displayedProduct) {
                $productsTypes[$displayedProduct] = $typeManager->findAvailables(array(
                    'product' => $displayedProduct
                ));
            }
        }


        return $this->render('@PaprecPublic/D3E/need.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'products' => $products,
            'productTypes' => $productsTypes
        ));
    }

    /**
     * Etape "Mes coordonnées"
     * où l'on créé le devis  au submit du formulaire
     *
     * @Route("/d3e/step2/{cartUuid}", name="paprec_public_corp_d3e_subscription_step2")
     * @throws \Exception
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode = $cart->getPostalCode();
        $city = $cart->getCity();

        // si l'utilisateur est dans "J'établis un devis" alors on créé un devis D3E
        if ($type == 'quote') {
            $productD3EQuote = new ProductD3EQuote();
            $productD3EQuote->setCity($city);
            $productD3EQuote->setPostalCode($postalCode);


            $form = $this->createForm(productD3EQuoteShortType::class, $productD3EQuote);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');

                $productD3EQuote = $form->getData();
                $productD3EQuote->setQuoteStatus('CREATED');
                $productD3EQuote->setFrequency($cart->getFrequency());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productD3EQuote);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                if ($cart->getContent() !== null) {
                    foreach ($cart->getContent() as $key => $value) {
                        foreach ($value as $item) {
                            $productD3EQuoteManager->addLineFromCart($productD3EQuote, $key, $item['tId'], $item['qtty'], $item['optHandling'], $item['optSerialNumberStmt'], $item['optDestruction']);
                        }
                    }
                }

                //Envois du mail d'alerte au responsable de division et du mail avec le devis au client
                $sendNewProductD3EQuoteMail = $productD3EQuoteManager->sendNewProductD3EQuoteEmail($productD3EQuote);
                $sendGeneratedQuoteMail = $productD3EQuoteManager->sendGeneratedQuoteEmail($productD3EQuote);

                if ($sendNewProductD3EQuoteMail && $sendGeneratedQuoteMail) {

                    return $this->redirectToRoute('paprec_public_corp_d3e_subscription_step3', array(
                        'cartUuid' => $cart->getId(),
                        'quoteId' => $productD3EQuote->getId()
                    ));
                }

            }
        }
        return $this->render('@PaprecPublic/D3E/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Mon offre" qui récapitule le devis créé par l'utilisateur
     *
     * @Route("/d3e/step3/{cartUuid}/{quoteId}", name="paprec_public_corp_d3e_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid, $quoteId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $productD3EQuote = $em->getRepository('PaprecCommercialBundle:ProductD3EQuote')->find($quoteId);
        $cart = $cartManager->get($cartUuid);

        return $this->render('@PaprecPublic/D3E/offerDetails.html.twig', array(
            'productD3EQuote' => $productD3EQuote,
            'cart' => $cart
        ));
    }


    /**
     * Ajoute au cart un displayedProduct
     *
     * @Route("/d3e/addOrRemoveDisplayedProduct/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedProduct du Cart
        $cart = $cartManager->addOrRemoveDisplayedProductNoCat($cartUuid, $productId);

        if ($cart->getType() === 'package') {
            return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_step1', array(
                'cartUuid' => $cart->getId(),
                '_fragment' => 'anchor1'
            ));
        }
        return $this->redirectToRoute('paprec_public_corp_d3e_subscription_step1', array(
            'cartUuid' => $cart->getId(),
            '_fragment' => 'anchor1'
        ));
    }

    /**
     * Ajoute au cart un Product avec sa quantité
     *
     * @Route("/d3e/addContent/packaged/{cartUuid}/{productId}/{quantity}/{optHandling}/{optSerialNumberStmt}/{optDestruction}", name="paprec_public_corp_d3e_subscription_addContent_packaged")
     * @throws \Exception
     */
    public function addPackageContentAction(Request $request, $cartUuid, $productId, $quantity, $optHandling, $optSerialNumberStmt, $optDestruction)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->addContentD3E($cartUuid, $productId, $quantity, $optHandling, $optSerialNumberStmt, $optDestruction);

        return new JsonResponse('200');
    }

    /**
     * Ajoute au cart un Product packagé avec sa quantité, ses options et son type
     *
     * @Route("/d3e/addContent/{cartUuid}", name="paprec_public_corp_d3e_subscription_addContent")
     * @param Request $request
     */
    public function addContentAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $data = json_decode($request->getContent(), true);
        if ($data && count($data)) {
            foreach ($data as $productD3EType) {
                $cartManager->addContentD3E($cartUuid, $productD3EType);
            }
        }


//        $response = new Response(json_encode(array(
//            'product' => $data,
//            'producteur' => 'producteur'
//        )));
//        $response->headers->set('Content-Type', 'application/json');

        return new JsonResponse('200');
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/d3e/removeContent/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_removeContent")
     * @throws \Exception
     */
    public function removeContentAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeContentD3E($cartUuid, $productId);

        return new JsonResponse($cart->getContent());
    }

    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/d3e/loadCart/{cartUuid}", name="paprec_public_corp_d3e_subscription_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadCartAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartD3E($cartUuid);

        return $this->render('@PaprecPublic/D3E/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart,
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * Retourne le twig des agences proches
     * @Route("/d3e/loadNearbyAgencies/{cartUuid}", name="paprec_public_corp_d3e_subscription_loadNearbyAgencies", condition="request.isXmlHttpRequest()")
     */
    public function loadNearbyAgenciesAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $agencyManager = $this->get('paprec_commercial.agency_manager');

        $cart = $cartManager->get($cartUuid);
        $distance = $this->getParameter('paprec_distance_nearby_agencies');
        $nbAgencies = $agencyManager->getNearbyAgencies($cart->getLongitude(), $cart->getLatitude(), 'D3E', $distance);

        return $this->render('@PaprecPublic/Common/partial/nearbyAgenciesPartial.html.twig', array(
            'nbAgencies' => $nbAgencies,
            'distance' => $distance
        ));
    }







    /****************************************************
     * PACKAGE
     ***************************************************/

    /**
     * Page d'accueil pour la gestion des package
     * @Route("/d3e/package/step0/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_corp_d3e_subscription_packaged_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexPackageAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        if (!$cartUuid) {
            $cart = $cartManager->create(90);
            $cart->setType('package');
            $cart->setDivision('D3E');
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_index', array(
                'cartUuid' => $cart->getId()
            ));
        } else {
            $cart = $cartManager->get($cartUuid);
        }

        return $this->render('@PaprecPublic/D3E/package/index.html.twig', array(
            'cart' => $cart
        ));
    }

    /**
     * Chois de  Ma solution Recyclage pour les produits packageés
     *
     * @Route("/d3e/package/step1/{cartUuid}", name="paprec_public_corp_d3e_subscription_packaged_step1")
     * @throws \Exception
     */
    public function step1PackageAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');

        $cart = $cartManager->get($cartUuid);

        /*
         * On récupère tous les produits packagés de la division qui sont disponibles en fonction du postalCode
         */
        $products = $productD3EManager->findPackagesAvailable($cart->getPostalCode());

        return $this->render('@PaprecPublic/D3E/package/need.html.twig', array(
            'cart' => $cart,
            'products' => $products
        ));
    }

    /**
     * Etape "Mes coordonnées"
     * où l'on créé la commande au submit du formulaire
     *
     * @Route("/d3e/package/step2/{cartUuid}", name="paprec_public_corp_d3e_subscription_packaged_step2")
     * @throws \Exception
     */
    public function step2PackageAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode = $cart->getPostalCode();
        $city = $cart->getCity();

        if ($type == 'package') {
            $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');

            $productD3EOrder = new ProductD3EOrder();
            $productD3EOrder->setCity($city);
            $productD3EOrder->setPostalCode($postalCode);
            $productD3EOrder->setInvoicingPostalCode($postalCode);
            $productD3EOrder->setInvoicingCity($city);

            $form = $this->createForm(ProductD3EOrderShortType::class, $productD3EOrder);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productD3EOrder = $form->getData();
                $productD3EOrder->setOrderStatus('CREATED');

                $em = $this->getDoctrine()->getManager();
                $em->persist($productD3EOrder);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                if ($cart->getContent() !== null) {
                    foreach ($cart->getContent() as $item) {
                        $productD3EOrderManager->addLineFromCart($productD3EOrder, $item['pId'], $item['qtty']);
                    }
                }

                $sendNewProductD3EOrderMail = $productD3EOrderManager->sendNewProductD3EOrderEmail($productD3EOrder);
                $sendOrderSummaryEmail = $productD3EOrderManager->sendOrderSummaryEmail($productD3EOrder);

                if ($sendNewProductD3EOrderMail && $sendOrderSummaryEmail) {
                    return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_step3', array(
                        'cartUuid' => $cart->getId(),
                        'orderId' => $productD3EOrder->getId()
                    ));
                }
            }
        }
        return $this->render('@PaprecPublic/D3E/package/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Ma livraison" qui est encore un formulaire complétant les infos du productD3EOrder
     *
     * @Route("/d3e/package/step3/{cartUuid}/{orderId}", name="paprec_public_corp_d3e_subscription_packaged_step3")
     */
    public function step3PackageAction(Request $request, $cartUuid, $orderId)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productD3EOrder = $em->getRepository('PaprecCommercialBundle:ProductD3EOrder')->find($orderId);
        $form = $this->createForm(ProductD3EOrderDeliveryType::class, $productD3EOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productD3EOrder = $form->getData();
            $em->merge($productD3EOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_step4', array(
                'cartUuid' => $cart->getId(),
                'orderId' => $productD3EOrder->getId()
            ));
        }
        return $this->render('@PaprecPublic/D3E/package/delivery.html.twig', array(
            'cart' => $cart,
            'productD3EOrder' => $productD3EOrder,
            'form' => $form->createView()
        ));
    }


    /**
     * Etape "Mon paiement" qui est encore un formulaire complétant les infos du productD3EOrder
     *
     * @Route("/d3e/package/step4/{cartUuid}/{orderId}", name="paprec_public_corp_d3e_subscription_packaged_step4")
     */
    public function step4PackageAction(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productD3EOrder = $em->getRepository('PaprecCommercialBundle:ProductD3EOrder')->find($orderId);
//        $form = $this->createForm(ProductD3EOrderDeliveryType::class, $productD3EOrder);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $productD3EOrder = $form->getData();
//            $em->merge($productD3EOrder);
//            $em->flush();
//
//            return $this->redirectToRoute(paprec_public_corp_d3e_subscription_step5, array(
//                'cartUuid' => $cart->getId(),
//                'orderId' => $productD3EOrder->getId()
//            ));
//        }
        return $this->render('@PaprecPublic/D3E/package/payment.html.twig', array(
            'cart' => $cart,
            'productD3EOrder' => $productD3EOrder
//            'form' => $form->createView()
        ));
    }
    

    /**
     * Ajoute au cart un displayedProduct
     *
     * @Route("/d3e/package/addOrRemoveDisplayedProduct/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_packaged_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductPackageAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProductNoCat($cartUuid, $productId);

        return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_step1', array(
            'cartUuid' => $cart->getId(),
            '_fragment' => 'anchor1'
        ));

    }

    /**
     * Ajoute au cart un Product avec sa quantité et  sa catégorie
     *
     * @Route("/d3e/package/addContent/{cartUuid}/{productId}/{quantity}", name="paprec_public_corp_d3e_subscription_packaged_addContent")
     * @throws \Exception
     */
    public function addContentPackageAction(Request $request, $cartUuid, $productId, $quantity)
    {
        $cartManager = $this->get('paprec.cart_manager');


        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addContentPackage($cartUuid, $productId, $quantity);

        return new JsonResponse('200');
    }

    /**
     * Augmente la quantité d'un produit dans le panier de 1
     * L'ajoute au panier si produit non présent
     *
     * @Route("/d3e/package/addOneContent/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_packaged_addOneContent", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function addOneProductPackageAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');


        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOneProductPackage($cartUuid, $productId);

        return new JsonResponse('200');
    }

    /**
     * Diminue la quantité d'un produit dans le panier de 1
     * Le supprime du panier si quantité = 0
     *
     * @Route("/d3e/package/removeOneContent/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_packaged_removeOneContent", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function removeOneProductPackageAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');


        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeOneProductPackage($cartUuid, $productId);

        return new JsonResponse('200');
    }

    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/d3e/package/loadCart/{cartUuid}", name="paprec_public_corp_d3e_subscription_packaged_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadCartPackageAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartPackageD3E($cartUuid);

        return $this->render('@PaprecPublic/D3E/package/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart,
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/d3e/package/removeContent/{cartUuid}/{productId}", name="paprec_public_corp_d3e_subscription_packaged_removeContent")
     * @throws \Exception
     */
    public function removeContentPackageAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeContentPackage($cartUuid, $productId);

        return new JsonResponse($cart->getContent());
    }

}
