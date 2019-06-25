<?php

namespace Paprec\PublicBundle\Controller;

use Paprec\CommercialBundle\Entity\CallBack;
use Paprec\CommercialBundle\Entity\ContactUs;
use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Entity\ProductDIQuote;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate;
use Paprec\CommercialBundle\Form\CallBack\CallBackShortType;
use Paprec\CommercialBundle\Form\ContactUs\ContactUsShortType;
use Paprec\CommercialBundle\Form\QuoteRequest\QuoteRequestNeedType;
use Paprec\CommercialBundle\Form\QuoteRequest\QuoteRequestShortType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{

    /**
     * @Route("/", name="paprec_public_devis_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndexAction()
    {
        return $this->redirectToRoute('paprec_public_corp_home_index');
    }

    /**
     * @Route("/step0/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_corp_home_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }
        $step = "d";

        if (!$cartUuid) {
            $cart = $cartManager->create(90);
            $cart->setType('quote');
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('paprec_public_corp_home_index', array(
                'cartUuid' => $cart->getId()
            ));
        } else {
            $cart = $cartManager->get($cartUuid);

            /**
             * step définie le prochain champ à afficher
             * Par défaut on est à la step d (division)
             * Quand d est définie on passe à l'étape l puis f
             * si on choisit "Régulier", on passe en étape r
             */
            //            $divisions = $this->getParameter('paprec_divisions');
            $divisions = array();
            foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
                $divisions[$division] = $divisionLong;
            }
            if ($cart->getDivision() && $cart->getDivision() !== '') {
                $step = "l";
            }
            if ($cart->getLocation() && $cart->getLocation() !== '') {
                $step = "f";
            }
            if ($cart->getFrequency() && $cart->getFrequency() !== '') {
                // Redirection vers le controller de la bonne division
                switch ($cart->getDivision()) {
                    case('DI'):
                        return $this->redirectToRoute('paprec_public_corp_di_subscription_step0', array(
                            'cartUuid' => $cart->getId()
                        ));
                        break;
                    case('CHANTIER'):
                        return $this->redirectToRoute('paprec_public_corp_chantier_subscription_step0', array(
                            'cartUuid' => $cart->getId()
                        ));
                    case('D3E'):
                        return $this->redirectToRoute('paprec_public_corp_d3e_subscription_step0', array(
                            'cartUuid' => $cart->getId()
                        ));
                }
            }
        }

        return $this->render('@PaprecPublic/Common/Home/index.html.twig', array(
            'divisions' => $divisions,
            'step' => $step,
            'cart' => $cart
        ));
    }

    /**
     * @Route("/addLocation/{cartUuid}/{location}/{city}/{postalCode}/{long}/{lat}", name="paprec_public_corp_home_addLocation")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function addLocationAction(Request $request, $cartUuid, $location, $city, $postalCode, $long, $lat)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // on supprime le contenu et les items affichés
        $cart->setContent();
        $cart->setDisplayedCategories();
        $cart->setDisplayedProducts();

        // on ajoute les données géographiques
        $cart->setLocation($location);
        $cart->setCity($city);
        $cart->setPostalCode($postalCode);
        $cart->setLatitude($lat);
        $cart->setLongitude($long);
        $em->flush();

        if ($cart->getType() === 'package') {
            switch ($cart->getDivision()) {
                case 'CHANTIER':
                    return $this->redirectToRoute('paprec_public_corp_chantier_subscription_packaged_step1', array(
                        'cartUuid' => $cartUuid
                    ));
                case 'D3E':
                    return $this->redirectToRoute('paprec_public_corp_d3e_subscription_packaged_step1', array(
                        'cartUuid' => $cartUuid
                    ));
            }
        }
        return $this->redirectToRoute('paprec_public_corp_home_index', array(
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * @Route("/addDivision/{cartUuid}/{division}", name="paprec_public_corp_home_addDivision")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function addDivisionAction(Request $request, $cartUuid, $division)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // Si le Cart a déjà une division
        // alors on créé un noveau Cart
        if ($cart->getDivision() && $cart->getDivision() != '') {
            $cart = $cartManager->cloneCart($cart);
        }

        $cart->setDivision($division);
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_home_index', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/addFrequency/{cartUuid}/{frequency}", name="paprec_public_corp_home_addFrequency")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function addFrequencyAction(Request $request, $cartUuid, $frequency)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        $cart->setFrequency($frequency);
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_home_index', array(
            'cartUuid' => $cartUuid
        ));
    }

    /**
     * Formulaire "Mon besoin" de "Je rédige ma demande"
     * @Route("/requestWriting/step1/{cartUuid}", name="paprec_public_corp_home_requestWriting_step1")
     * @param Request $request
     * @throws \Exception
     */
    public function requestWritingStep1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }
        $cart = $cartManager->get($cartUuid);

        $quoteRequest = new QuoteRequest();
        $form = $this->createForm(QuoteRequestNeedType::class, $quoteRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('CREATED');
            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setDivision($cart->getDivision());
            $quoteRequest->setPostalCode($cart->getPostalCode());
            $quoteRequest->setCity($cart->getCity());

            $files = array();
            foreach ($quoteRequest->getAttachedFiles() as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    /**
                     * On place le file uploadé dans le dossier web/files
                     * et on ajoute le nom du fichier md5 dans le tableau $files
                     */
                    $uploadedFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();

                    $uploadedFile->move($this->getParameter('paprec_commercial.quote_request.files_path'), $uploadedFileName);
                    $files[] = $uploadedFileName;
                }
            }
            $quoteRequest->setAttachedFiles($files);
            $em->persist($quoteRequest);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_home_requestWriting_step2', array(
                'cartUuid' => $cart->getId(),
                'quoteRequestId' => $quoteRequest->getId()
            ));
        }

        return $this->render('@PaprecPublic/Common/RequestWriting/need.html.twig', array(
            'form' => $form->createView(),
            'cart' => $cart,
            'divisions' => $divisions
        ));
    }

    /**
     * Formulaire pour besoin Régulier : commun à toutes les divisions donc dans HomeController
     * @Route("/requestWriting/step2/{cartUuid}/{quoteRequestId}", name="paprec_public_corp_home_requestWriting_step2")
     * @param Request $request
     * @throws \Exception
     */
    public function requestWritingStep2Action(Request $request, $cartUuid, $quoteRequestId)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $cartManager = $this->get('paprec.cart_manager');

        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($quoteRequestId);

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }
        $cart = $cartManager->get($cartUuid);

        $form = $this->createForm(QuoteRequestShortType::class, $quoteRequest, array(
            'division' => $cart->getDivision()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('CREATED');
            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setDivision($cart->getDivision());
            $quoteRequest->setPostalCode($cart->getPostalCode());
            $quoteRequest->setCity($cart->getCity());

            $files = array();
            foreach ($quoteRequest->getAttachedFiles() as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    /**
                     * On place le file uploadé dans le dossier web/files
                     * et on ajoute le nom du fichier md5 dans le tableau $files
                     */
                    $uploadedFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();

                    $uploadedFile->move($this->getParameter('paprec_commercial.quote_request.files_path'), $uploadedFileName);
                    $files[] = $uploadedFileName;
                }
            }
            $quoteRequest->setAttachedFiles($files);
            $em->persist($quoteRequest);
            $em->flush();

            $sendNewQuoteRequest = $quoteRequestManager->sendNewRequestEmail($quoteRequest);

            if ($sendNewQuoteRequest) {
                return $this->redirectToRoute('paprec_public_corp_home_requestWriting_step3', array(
                    'cartUuid' => $cart->getId(),
                    'quoteRequestId' => $quoteRequest->getId()
                ));
            }
        }

        return $this->render('@PaprecPublic/Common/RequestWriting/contactDetails.html.twig', array(
            'foorm' => $form,
            'form' => $form->createView(),
            'cart' => $cart,
            'divisions' => $divisions
        ));
    }

    /**
     * Formulaire pour besoin Régulier : commun à toutes les divisions donc dans HomeController
     * @Route("/requestWriting/step3/{cartUuid}/{quoteRequestId}", name="paprec_public_corp_home_requestWriting_step3")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function regularConfirmAction(Request $request, $cartUuid, $quoteRequestId)
    {
        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($quoteRequestId);
        return $this->render('@PaprecPublic/Common/RequestWriting/confirm.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * Formulaire "Contactez-nous"
     * @Route("/contact/{cartUuid}", defaults={"cartUuid"=null}, name="paprec_public_home_contactForm")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contactFormAction(Request $request, $cartUuid)
    {
        $contactUsManager = $this->get('paprec_commercial.contact_us_manager');

        $cart = null;
        if ($cartUuid) {
            $cartManager = $this->get('paprec.cart_manager');
            $cart = $cartManager->get($cartUuid);
        }

        $contactUs = new ContactUs();
        $form = $this->createForm(ContactUsShortType::class, $contactUs);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $contactUs = $form->getData();
            $contactUs->setTreatmentStatus('CREATED');

            if ($cartUuid) {
                $cartManager = $this->get('paprec.cart_manager');
                $cart = $cartManager->get($cartUuid);
                $contactUs->setDivision($cart->getDivision());
                $contactUs->setCartContent($cart->getContent());
            }

            $files = array();
            foreach ($contactUs->getAttachedFiles() as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    /**
                     * On place le file uploadé dans le dossier var/files/contactUs
                     * et on ajoute le nom du fichier md5 dans le tableau $files
                     */
                    $uploadedFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();

                    $uploadedFile->move($this->getParameter('paprec_commercial.contact_us.files_path'), $uploadedFileName);
                    $files[] = $uploadedFileName;
                }
            }
            $contactUs->setAttachedFiles($files);
            $em->persist($contactUs);
            $em->flush();

            $sendConfirmEmail = $contactUsManager->sendConfirmRequestEmail($contactUs);
            $sendNewRequestEmail = $contactUsManager->sendNewRequestEmail($contactUs);

            $client = new \GuzzleHttp\Client();
            $uri = $this->getParameter('paprec_public_site_url') . '/?na=s';
            $response = $client->request('POST', $uri, [
                'form_params' => [
                    'nr' => 'widget-minimal',
                    'ne' => $contactUs->getEmail()
                ]
            ]);


            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_home_contactConfirm', array(
                    'contactUsId' => $contactUs->getId()
                ));
            }
        }

        if ($cart) {
            return $this->render('@PaprecPublic/Common/Contact/contactFormFromCart.html.twig', array(
                'form' => $form->createView(),
                'cart' => $cart
            ));
        } else {
            return $this->render('@PaprecPublic/Common/Contact/contactForm.html.twig', array(
                'form' => $form->createView(),
                'cart' => $cart
            ));
        }
    }

    /**
     * IHM de confirmation de prise en compte de la demande "Demande de contact"
     * @Route("/contactConfirm/{contactUsId}", name="paprec_public_home_contactConfirm")
     * @param Request $request
     * @throws \Exception
     */
    public function contactConfirmAction(Request $request, $contactUsId)
    {
        $em = $this->getDoctrine()->getManager();
        $contactUs = $em->getRepository('PaprecCommercialBundle:ContactUs')->find($contactUsId);
        return $this->render('@PaprecPublic/Common/Contact/contactConfirm.html.twig', array(
            'contactUs' => $contactUs
        ));
    }


    /**
     * Formulaire "Etre rappelé"
     *
     * @Route("/callBackForm/{cartUuid}", name="paprec_public_home_callBackForm")
     * @param Request $request
     * @throws \Exception
     */
    public function callBackFormAction(Request $request, $cartUuid)
    {
        $callBackManager = $this->get('paprec_commercial.call_back_manager');

        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);

        $callBack = new CallBack();
        $form = $this->createForm(CallBackShortType::class, $callBack);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $callBack = $form->getData();
            $callBack->setTreatmentStatus('CREATED');
            $callBack->setCartContent($cart->getContent());


            $em->persist($callBack);
            $em->flush();

            $sendConfirmEmail = $callBackManager->sendConfirmRequestEmail($callBack);
            $sendNewRequestEmail = $callBackManager->sendNewRequestEmail($callBack);

            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_home_callBackConfirm', array(
                    'cartUuid' => $cart->getId(),
                    'callBackId' => $callBack->getId()
                ));
            }
        }
        return $this->render('@PaprecPublic/Common/CallBack/callBackForm.html.twig', array(
            'form' => $form->createView(),
            'cart' => $cart
        ));
    }

    /**
     * IHM de confirmation de prise en compte de la demande "Etre rappelé"
     *
     * @Route("/callBackConfirm/{cartUuid}/{callBackId}", name="paprec_public_home_callBackConfirm")
     * @param Request $request
     * @throws \Exception
     */
    public function callBackConfirmAction(Request $request, $cartUuid, $callBackId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $cart = $cartManager->get($cartUuid);
        $callBack = $em->getRepository('PaprecCommercialBundle:CallBack')->find($callBackId);
        return $this->render('@PaprecPublic/Common/CallBack/callBackConfirm.html.twig', array(
            'callBack' => $callBack,
            'cart' => $cart
        ));
    }


    /**
     * Affichage des mails
     */


    /**
     * 2.3.2 : Formulaire Groupe @ Réseaux, alerte au responsable
     *
     * @Route("/mail/group/new/{id}", name="paprec_public_home_mail_group")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewGroupReseau(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate/emails:sendNewRequestEmail.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }

    /**
     * 2.3.2 : Formulaire Groupe @ Réseaux, confirmation
     *
     * @Route("/mail/group/confirm/{id}", name="paprec_public_home_mail_group_confirm")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmGroupReseau(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate/emails:sendConfirmRequestEmail.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }

    /**
     * 2.4.2 :Formulaire Collectivite
     *
     * @Route("/mail/collectivite/{id}", name="paprec_public_home_mail_collectivite")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailCollectivite(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate/emails:sendNewRequestEmail.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }


    /**
     * 2.5.2 :Formulaire Particulier
     *
     * @Route("/mail/particulier/{id}", name="paprec_public_home_mail_particulier")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailParticulier(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate/emails:sendNewRequestEmail.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }


    /**
     * 2,6,1,2 : Fomulaire Contact, mail alerte au responsable Paprec
     *
     * @Route("/mail/new/contact/{id}", name="paprec_public_home_mail_new_contact")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewContact(ContactUs $contactUs)
    {
        return $this->render('PaprecCommercialBundle:ContactUs/emails:sendNewRequestEmail.html.twig', array(
            'contactUs' => $contactUs
        ));
    }

    /**
     * 2,6,1,2 : Fomulaire Contact, mail de confirmation à l'utilisateur
     *
     * @Route("/mail/confirm/contact/{id}", name="paprec_public_home_mail_confirm_contact")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmContact(ContactUs $contactUs)
    {
        return $this->render('PaprecCommercialBundle:ContactUs/emails:sendConfirmRequestEmail.html.twig', array(
            'contactUs' => $contactUs
        ));
    }


    /**
     * 2,6,2,2 : Formulaire Rappel, mail alerte au responsable Paprec
     *
     * @Route("/mail/new/callback/{id}", name="paprec_public_home_mail_new_callback")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewCallback(CallBack $callBack)
    {
        return $this->render('PaprecCommercialBundle:CallBack/emails:sendNewRequestEmail.html.twig', array(
            'callBack' => $callBack
        ));
    }

    /**
     * 2,6,2,2 : Fomulaire Rappel, mail de confirmation à l'utilisateur
     *
     * @Route("/mail/confirm/callback/{id}", name="paprec_public_home_mail_confirm_callback")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConformCallback(CallBack $callBack)
    {
        return $this->render('PaprecCommercialBundle:CallBack/emails:sendConfirmRequestEmail.html.twig', array(
            'callBack' => $callBack
        ));
    }


    /**
     * 3,1,2,2 : Devis DI, mail alerte au responsable de la division
     *
     * @Route("/mail/quote/new/di/{id}", name="paprec_public_home_mail_quote_new_di")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewDIQuote(ProductDIQuote $productDIQuote)
    {
        return $this->render('@PaprecCommercial/ProductDIQuote/emails/sendNewQuoteEmail.html.twig', array(
            'productDIQuote' => $productDIQuote
        ));
    }

    /**
     * 3,1,2,2 : Devis DI, mail confirrmation à l'utilisateur (avec Devis joint normalement)
     *
     * @Route("/mail/quote/confirm/di/{id}", name="paprec_public_home_mail_quote_confirm_di")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmDIQuote(ProductDIQuote $productDIQuote)
    {
        return $this->render('@PaprecCommercial/ProductDIQuote/emails/sendGeneratedQuoteEmail.html.twig', array(
            'productDIQuote' => $productDIQuote
        ));
    }

    /**
     * 4,1,2,2 : Devis D3E, mail alerte au responsable de la division
     *
     * @Route("/mail/quote/new/d3e/{id}", name="paprec_public_home_mail_quote_new_d3e")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewD3EQuote(ProductD3EQuote $productD3EQuote)
    {
        return $this->render('@PaprecCommercial/ProductD3EQuote/emails/sendNewQuoteEmail.html.twig', array(
            'productD3EQuote' => $productD3EQuote
        ));
    }

    /**
     * 4,1,2,2 : Devis D3E, mail confirrmation à l'utilisateur (avec Devis joint normalement)
     *
     * @Route("/mail/quote/confirm/d3e/{id}", name="paprec_public_home_mail_quote_confirm_d3e")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmD3EQuote(ProductD3EQuote $productD3EQuote)
    {
        return $this->render('@PaprecCommercial/ProductD3EQuote/emails/sendGeneratedQuoteEmail.html.twig', array(
            'productD3EQuote' => $productD3EQuote
        ));
    }

    /**
     * 4,1,2,2 : Commande D3E, mail alerte au responsable de la division
     *
     * @Route("/mail/order/new/d3e/{id}", name="paprec_public_home_mail_order_new_d3e")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewD3EOrder(ProductD3EOrder $productD3EOrder)
    {
        return $this->render('@PaprecCommercial/ProductD3EOrder/emails/sendNewOrderEmail.html.twig', array(
            'productD3EOrder' => $productD3EOrder
        ));
    }

    /**
     * 4,1,2,2 : Commande D3E, mail confirrmation à l'utilisateur
     *
     * @Route("/mail/order/confirm/d3e/{id}", name="paprec_public_home_mail_order_confirm_d3e")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmD3EOrder(ProductD3EOrder $productD3EOrder)
    {
        return $this->render('@PaprecCommercial/ProductD3EOrder/emails/sendOrderSummaryEmail.html.twig', array(
            'productD3EOrder' => $productD3EOrder
        ));
    }


    /**
     * 5,1,2,2 : Devis Chantier, mail alerte au responsable de la division
     *
     * @Route("/mail/quote/new/chantier/{id}", name="paprec_public_home_mail_quote_new_chantier")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewChantierQuote(ProductChantierQuote $productChantierQuote)
    {
        return $this->render('@PaprecCommercial/ProductChantierQuote/emails/sendNewQuoteEmail.html.twig', array(
            'productChantierQuote' => $productChantierQuote
        ));
    }

    /**
     * 5,1,2,2 : Devis Chantier, mail confirrmation à l'utilisateur (avec Devis joint normalement)
     *
     * @Route("/mail/quote/confirm/chantier/{id}", name="paprec_public_home_mail_quote_confirm_chantier")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmChantierQuote(ProductChantierQuote $productChantierQuote)
    {
        return $this->render('@PaprecCommercial/ProductChantierQuote/emails/sendGeneratedQuoteEmail.html.twig', array(
            'productChantierQuote' => $productChantierQuote
        ));
    }


    /**
     * 5,1,2,2 : Commande Chantier, mail alerte au responsable de la division
     *
     * @Route("/mail/order/new/chantier/{id}", name="paprec_public_home_mail_order_new_chantier")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailNewChantierOrder(ProductChantierOrder $productChantierOrder)
    {
        return $this->render('@PaprecCommercial/ProductChantierOrder/emails/sendNewOrderEmail.html.twig', array(
            'productChantierOrder' => $productChantierOrder
        ));
    }

    /**
     * 5,1,2,2 : Commande Chantier, mail confirrmation à l'utilisateur
     *
     * @Route("/mail/order/confirm/chantier/{id}", name="paprec_public_home_mail_order_confirm_chantier")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailConfirmChantierOrder(ProductChantierOrder $productChantierOrder)
    {
        return $this->render('@PaprecCommercial/ProductChantierOrder/emails/sendOrderSummaryEmail.html.twig', array(
            'productChantierOrder' => $productChantierOrder
        ));
    }


    /**
     * 3,2,2/4,2,2/5,2,2 : Formulaire "Je rédige mon besoin" pour DI/D3E/Chantier, alerte au responsable
     *
     * @Route("/mail/new/besoin/{id}", name="paprec_public_home_mail_besoin_new")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailBesoin(QuoteRequest $quoteRequest)
    {
        return $this->render('@PaprecCommercial/QuoteRequest/emails/sendNewRequestEmail.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * 7,6,1,2 : Formulaire "Je rédige mon besoin" pour DI/D3E/Chantier, envoi de devis uploadé dans le BO
     *
     * @Route("/mail/quote/besoin/{id}", name="paprec_public_home_mail_besoin_quote")
     * @param Request $request
     * @throws \Exception
     */
    public function showMailBesoinQuote(QuoteRequest $quoteRequest)
    {
        return $this->render('@PaprecCommercial/QuoteRequest/emails/sendAssociatedQuoteEmail.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }


    /**
     * 7,6,5,2 : Facture D3E : Envoi de la facture uploadée par le Responsable depuis le BO
     *
     * @Route("/mail/d3e/invoice/{id}", name="paprec_public_home_mail_invoice_d3e")
     * @param Request $request
     * @throws \Exception
     */
    public function showInvoiceMailD3E(ProductD3EOrder $productD3EOrder)
    {
        return $this->render('@PaprecCommercial/ProductD3EOrder/emails/sendAssociatedInvoiceEmail.html.twig', array(
            'productD3EOrder' => $productD3EOrder
        ));
    }

    /**
     * 7,6,5,2 : Facture Chantier : Envoi de la facture uploadée par le Responsable depuis le BO
     *
     * @Route("/mail/chantier/invoice/{id}", name="paprec_public_home_mail_invoice_chantier")
     * @param Request $request
     * @throws \Exception
     */
    public function showInvoiceMailChantier(ProductChantierOrder $productChantierOrder)
    {
        return $this->render('@PaprecCommercial/ProductChantierOrder/emails/sendAssociatedInvoiceEmail.html.twig', array(
            'productChantierOrder' => $productChantierOrder
        ));
    }

    /**
     * 7,6,7,2 : Devis Groupe/Collectivite/Particulier,  Envoi du Devis en PDF uploadé par le responsable depuis le BO
     *
     * @Route("/mail/noncorporate/quote/{id}", name="paprec_public_home_mail_non_corporate_quote")
     * @param Request $request
     * @throws \Exception
     */
    public function showQuoteMailNonCorporate(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->render('@PaprecCommercial/QuoteRequestNonCorporate/emails/sendAssociatedQuoteEmail.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }

    
    /*******
     * ex: http://dev.store.easyrecyclage.com/app_dev.php/devis/quote/di/cover/2
     ******/

    /**
     * @Route("/quote/di/cover/{id}", name="paprec_public_quote_di_cover")
     * @param Request $request
     * @throws \Exception
     */
    public function showDevisDICover(ProductDIQuote $productDIQuote)
    {
        $today = new \DateTime();
        return $this->render('@PaprecCommercial/ProductDIQuote/PDF/printQuoteCover.html.twig', array(
                'productDIQuote' => $productDIQuote,
                'date' => $today
            )
        );
    }

    /**
     * @Route("/quote/di/letter/{id}", name="paprec_public_quote_di_letter")
     * @param Request $request
     * @throws \Exception
     */
    public function showDevisDILetter(ProductDIQuote $productDIQuote)
    {
        $today = new \DateTime();
        return $this->render('@PaprecCommercial/ProductDIQuote/PDF/printQuoteLetter.html.twig', array(
                'productDIQuote' => $productDIQuote,
                'date' => $today
            )
        );
    }

    /**
     * @Route("/quote/di/products/{id}", name="paprec_public_quote_di_products")
     * @param Request $request
     * @throws \Exception
     */
    public function showDevisDIProducts(ProductDIQuote $productDIQuote)
    {
        return $this->render('@PaprecCommercial/ProductDIQuote/PDF/printQuoteProducts.html.twig', array(
                'productDIQuote' => $productDIQuote
            )
        );
    }


}
