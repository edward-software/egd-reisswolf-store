<?php

namespace Paprec\PublicBundle\Controller\Chantier;

use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderDeliveryType;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderShortType;
use Paprec\CommercialBundle\Form\ProductChantierQuote\ProductChantierQuoteShortType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    /**
     * @Route("/chantier/step0/{cartUuid}", name="paprec_public_corp_chantier_subscription_step0")
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

        return $this->render('@PaprecPublic/Chantier/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,

        ));
    }

    /**
     * @Route("/chantier/setOrder/{cartUuid}", name="paprec_public_corp_chantier_subscription_setOrder")
     * @throws \Exception
     */
    public function setOrderAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('order');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/chantier/setQuote/{cartUuid}", name="paprec_public_corp_chantier_subscription_setQuote")
     * @throws \Exception
     */
    public function setQuoteAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('quote');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Etape "Mon besoin", choix des produits et ajout au Cart
     *
     * On passe le $type en paramère qui correspond à 'order' (commande) ou 'quote'(devis)
     * @Route("/chantier/step1/{cartUuid}", name="paprec_public_corp_chantier_subscription_step1")
     * @throws \Exception
     */
    public function step1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $productChantierManager = $this->get('paprec_catalog.product_chantier_manager');


        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        // On récupère les catégoriesDI pour afficher le choix des catégories
        $categories = $categoryManager->getCategoriesChantier($type);

        // Pour alimenter le "select" des types de déchets
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }

        /*
         * Si il y a des displayedCategories, il faut récupérer leurs produits pour les afficher
         */
        $productsCategories = array();
        foreach ($cart->getDisplayedCategories() as $displayedCategory) {
            $productsCategories[$displayedCategory] = $productChantierManager->findAvailables(array(
                'category' => $displayedCategory,
                'type' => $type,
                'postalCode' => $cart->getPostalCode()
            ));
        }

        return $this->render('@PaprecPublic/Chantier/need.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'categories' => $categories,
            'productsCategories' => $productsCategories
        ));
    }

    /**
     * Etape "Mes coordonnées"
     * où l'on créé le devis où la quote au submit du formulaire
     *
     * @Route("/chantier/step2/{cartUuid}", name="paprec_public_corp_chantier_subscription_step2")
     * @throws \Exception
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $type = $request->get('type');
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode = $cart->getPostalCode();
        $city = $cart->getCity();

        // si l'utilisateur est dans "J'établis un devis" alors on créé un devis Chantier
        if ($type == 'quote') {
            $productChantierQuote = new ProductChantierQuote();
            $productChantierQuote->setCity($city);
            $productChantierQuote->setPostalCode($postalCode);


            $form = $this->createForm(productChantierQuoteShortType::class, $productChantierQuote);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');

                $productChantierQuote = $form->getData();
                $productChantierQuote->setQuoteStatus('CREATED');
                $productChantierQuote->setFrequency($cart->getFrequency());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierQuote);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                if ($cart->getContent() !== null) {
                    foreach ($cart->getContent() as $item) {
                        $productChantierQuoteManager->addLineFromCart($productChantierQuote, $item['pId'], $item['qtty'], $item['cId']);
                    }
                }

                // Envoi du mail d'alerte au responsable de division et envoi du devis au client
                $sendNewProductChantierQuoteMail = $productChantierQuoteManager->sendNewProductChantierQuoteEmail($productChantierQuote);
                $sendGeneratedQuoteMail = $productChantierQuoteManager->sendGeneratedQuoteEmail($productChantierQuote);

                if ($sendNewProductChantierQuoteMail && $sendGeneratedQuoteMail) {
                    return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step3', array(
                        'cartUuid' => $cart->getId(),
                        'quoteId' => $productChantierQuote->getId()
                    ));
                }

            }
        } else { // sinon on créé une commande Chantier
            $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');


            $productChantierOrder = new ProductChantierOrder();
            $productChantierOrder->setCity($city);
            $productChantierOrder->setPostalCode($postalCode);

            $form = $this->createForm(ProductChantierOrderShortType::class, $productChantierOrder);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productChantierOrder = $form->getData();
                $productChantierOrder->setOrderStatus('CREATED');

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierOrder);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                if ($cart->getContent() !== null) {
                    foreach ($cart->getContent() as $item) {
                        $productChantierOrderManager->addLineFromCart($productChantierOrder, $item['pId'], $item['qtty'], $item['cId']);
                    }
                }

                $sendNewProductChantierOrder = $productChantierOrderManager->sendNewProductChantierOrderEmail($productChantierOrder);
                $sendOrderSummaryEmail = $productChantierOrderManager->sendOrderSummaryEmail($productChantierOrder);

                if ($sendNewProductChantierOrder && $sendOrderSummaryEmail) {
                    return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step4', array(
                        'cartUuid' => $cart->getId(),
                        'orderId' => $productChantierOrder->getId()
                    ));
                }
            }

        }
        return $this->render('@PaprecPublic/Chantier/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Mon offre" qui récapitule le devis créé par l'utilisateur
     *
     * @Route("/chantier/step3/{cartUuid}/{quoteId}", name="paprec_public_corp_chantier_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid, $quoteId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $productChantierQuote = $em->getRepository('PaprecCommercialBundle:ProductChantierQuote')->find($quoteId);
        $cart = $cartManager->get($cartUuid);

        return $this->render('@PaprecPublic/Chantier/confirm.html.twig', array(
            'productChantierQuote' => $productChantierQuote,
            'cart' => $cart
        ));
    }


    /**
     * Etape "Ma livraison" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/step4/{cartUuid}/{orderId}", name="paprec_public_corp_chantier_subscription_step4")
     */
    public function step4Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
        $form = $this->createForm(ProductChantierOrderDeliveryType::class, $productChantierOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierOrder = $form->getData();
            $em->merge($productChantierOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step5', array(
                'cartUuid' => $cart->getId(),
                'orderId' => $productChantierOrder->getId()
            ));
        }
        return $this->render('@PaprecPublic/Chantier/delivery.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder,
            'form' => $form->createView()
        ));
    }


    /**
     * Etape "Mon paiement" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/step5/{cartUuid}/{orderId}", name="paprec_public_corp_chantier_subscription_step5")
     */
    public function step5Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
//        $form = $this->createForm(ProductChantierOrderDeliveryType::class, $productChantierOrder);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $productChantierOrder = $form->getData();
//            $em->merge($productChantierOrder);
//            $em->flush();
//
//            return $this->redirectToRoute(paprec_public_corp_chantier_subscription_step5, array(
//                'cartUuid' => $cart->getId(),
//                'orderId' => $productChantierOrder->getId()
//            ));
//        }
        return $this->render('@PaprecPublic/Chantier/payment.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder
//            'form' => $form->createView()
        ));
    }


    /**
     * Au clic sur une catégorie, on l'ajoute ou on la supprime des catégories affichées
     *
     * @Route("/chantier/addDisplayedCategory/{cartUuid}/{categoryId}", name="paprec_public_corp_chantier_subscription_addDisplayedCategory")
     * @throws \Exception
     */
    public function addDisplayedCategoryAction(Request $request, $cartUuid, $categoryId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime la catégorie sélecionnée au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedCategory($cartUuid, $categoryId);

        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step1', array(
            'cartUuid' => $cart->getId(),
            '_fragment' => 'anchor1'
        ));
    }

    /**
     * Ajoute au cart un displayedProduct avec en key => value( categoryId => productId)
     *
     * @Route("/chantier/addOrRemoveDisplayedProduct/{cartUuid}/{categoryId}/{productId}", name="paprec_public_corp_chantier_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProduct($cartUuid, $categoryId, $productId);

        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step1', array(
            'cartUuid' => $cart->getId(),
            '_fragment' => 'anchor1'
        ));
    }

    /**
     * Ajoute au cart un Product avec sa quantité et  sa catégorie
     *
     * @Route("/chantier/addContent/{cartUuid}/{categoryId}/{productId}/{quantity}", name="paprec_public_corp_chantier_subscription_addContent")
     * @throws \Exception
     */
    public function addContentAction(Request $request, $cartUuid, $categoryId, $productId, $quantity)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addContent($cartUuid, $categoryId, $productId, $quantity);

        return new JsonResponse('200');
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/chantier/removeContent/{cartUuid}/{categoryId}/{productId}", name="paprec_public_corp_chantier_subscription_removeContent")
     * @throws \Exception
     */
    public function removeContentAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeContent($cartUuid, $categoryId, $productId);

        return new JsonResponse($cart->getContent());
    }

    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/chantier/loadCart/{cartUuid}", name="paprec_public_corp_chantier_subscription_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadCartAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartChantier($cartUuid);

        return $this->render('@PaprecPublic/Chantier/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart,
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * Retourne le twig des agences proches
     * @Route("/chantier/loadNearbyAgencies/{cartUuid}", name="paprec_public_corp_chantier_subscription_loadNearbyAgencies", condition="request.isXmlHttpRequest()")
     */
    public function loadNearbyAgenciesAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $agencyManager = $this->get('paprec_commercial.agency_manager');

        $cart = $cartManager->get($cartUuid);
        $distance = $this->getParameter('paprec_distance_nearby_agencies');
        $nbAgencies = $agencyManager->getNearbyAgencies($cart->getLongitude(), $cart->getLatitude(), 'CHANTIER', $distance);

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
     * @Route("/chantier/package/step0/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_corp_chantier_subscription_packaged_index")
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
            $cart->setDivision('CHANTIER');
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('paprec_public_corp_chantier_subscription_packaged_index', array(
                'cartUuid' => $cart->getId()
            ));
        } else {
            $cart = $cartManager->get($cartUuid);
        }

        return $this->render('@PaprecPublic/Chantier/package/index.html.twig', array(
            'cart' => $cart
        ));
    }

    /**
     * Chois de  Ma solution Recyclage pour les produits packageés
     *
     * @Route("/chantier/package/step1/{cartUuid}", name="paprec_public_corp_chantier_subscription_packaged_step1")
     * @throws \Exception
     */
    public function step1PackageAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $productChantierManager = $this->get('paprec_catalog.product_chantier_manager');


        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();


        /*
         * On récupère tous les produits packagés de la division qui sont disponibles en fonction du postalCode
         */
        $products = $productChantierManager->findPackagesAvailable($cart->getPostalCode());

        return $this->render('@PaprecPublic/Chantier/package/need.html.twig', array(
            'cart' => $cart,
            'products' => $products
        ));
    }


    /**
     * Etape "Mes coordonnées"
     * où l'on créé la commande au submit du formulaire
     *
     * @Route("/chantier/package/step2/{cartUuid}", name="paprec_public_corp_chantier_subscription_packaged_step2")
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
            $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');

            $productChantierOrder = new ProductChantierOrder();
            $productChantierOrder->setCity($city);
            $productChantierOrder->setPostalCode($postalCode);
            $productChantierOrder->setInvoicingPostalCode($postalCode);
            $productChantierOrder->setInvoicingCity($city);

            $form = $this->createForm(ProductChantierOrderShortType::class, $productChantierOrder);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productChantierOrder = $form->getData();
                $productChantierOrder->setOrderStatus('CREATED');

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierOrder);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                if ($cart->getContent() !== null) {
                    foreach ($cart->getContent() as $item) {
                        $productChantierOrderManager->addLineFromCart($productChantierOrder, $item['pId'], $item['qtty']);
                    }
                }

                $sendNewProductD3EOrderMail = $productChantierOrderManager->sendNewProductChantierOrderEmail($productChantierOrder);
                $sendOrderSummaryEmail = $productChantierOrderManager->sendOrderSummaryEmail($productChantierOrder);

                if ($sendNewProductD3EOrderMail && $sendOrderSummaryEmail) {
                    return $this->redirectToRoute('paprec_public_corp_chantier_subscription_packaged_step3', array(
                        'cartUuid' => $cart->getId(),
                        'orderId' => $productChantierOrder->getId()
                    ));
                }
            }
        }
        return $this->render('@PaprecPublic/Chantier/package/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Ma livraison" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/package/step3/{cartUuid}/{orderId}", name="paprec_public_corp_chantier_subscription_packaged_step3")
     */
    public function step3PackageAction(Request $request, $cartUuid, $orderId)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
        $form = $this->createForm(ProductChantierOrderDeliveryType::class, $productChantierOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierOrder = $form->getData();
            $em->merge($productChantierOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_chantier_subscription_packaged_step4', array(
                'cartUuid' => $cart->getId(),
                'orderId' => $productChantierOrder->getId()
            ));
        }
        return $this->render('@PaprecPublic/Chantier/package/delivery.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Mon paiement" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/package/step4/{cartUuid}/{orderId}", name="paprec_public_corp_chantier_subscription_packaged_step4")
     */
    public function step4PackageAction(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
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
        return $this->render('@PaprecPublic/Chantier/package/payment.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder
//            'form' => $form->createView()
        ));
    }

    /**
     * Ajoute au cart un displayedProduct
     *
     * @Route("/chantier/package/addOrRemoveDisplayedProduct/{cartUuid}/{productId}", name="paprec_public_corp_chantier_subscription_packaged_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductPackageAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProductNoCat($cartUuid, $productId);

        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_packaged_step1', array(
            'cartUuid' => $cart->getId(),
            '_fragment' => 'anchor1'
        ));

    }

    /**
     * Ajoute au cart un Product avec sa quantité
     *
     * @Route("/chantier/package/addContent/{cartUuid}/{productId}/{quantity}", name="paprec_public_corp_chantier_subscription_packaged_addContent")
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
     * @Route("/chantier/package/addOneContent/{cartUuid}/{productId}", name="paprec_public_corp_chantier_subscription_packaged_addOneContent", condition="request.isXmlHttpRequest()")
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
     * @Route("/chantier/package/removeOneContent/{cartUuid}/{productId}", name="paprec_public_corp_chantier_subscription_packaged_removeOneContent", condition="request.isXmlHttpRequest()")
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
     * @Route("/chantier/package/loadCart/{cartUuid}", name="paprec_public_corp_chantier_subscription_packaged_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadCartPackageAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartPackageChantier($cartUuid);

        return $this->render('@PaprecPublic/Chantier/package/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart,
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/chantier/package/removeContent/{cartUuid}/{productId}", name="paprec_public_corp_chantier_subscription_packaged_removeContent")
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
