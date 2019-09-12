<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Entity\QuoteRequestLine;
use Paprec\CommercialBundle\Form\QuoteRequestLineAddType;
use Paprec\CommercialBundle\Form\QuoteRequestLineEditType;
use Paprec\CommercialBundle\Form\QuoteRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class QuoteRequestController extends Controller
{

    /**
     * @Route("/quoteRequest", name="paprec_commercial_quoteRequest_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:QuoteRequest:index.html.twig');
    }

    /**
     * @Route("/quoteRequest/loadList", name="paprec_commercial_quoteRequest_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function loadListAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'q.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'q.businessName', 'method' => array('getBusinessName'));
        $cols['totalAmount'] = array('label' => 'totalAmount', 'id' => 'q.totalAmount', 'method' => array('getTotalAmount'));
        $cols['quoteStatus'] = array('label' => 'quoteStatus', 'id' => 'q.quoteStatus', 'method' => array('getQuoteStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'q.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('q'))
            ->from('PaprecCommercialBundle:QuoteRequest', 'q')
            ->where('q.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('q.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('q.businessName', '?1'),
                    $queryBuilder->expr()->like('q.totalAmount', '?1'),
                    $queryBuilder->expr()->like('q.quoteStatus', '?1'),
                    $queryBuilder->expr()->like('q.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['totalAmount'] = $numberManager->formatAmount($data['totalAmount'], 'EUR', $request->getLocale());
            $line['quoteStatus'] = $this->container->get('translator')->trans("Commercial.QuoteStatusList." . $data['quoteStatus']);
            $tmp[] = $line;
        }
        $datatable['data'] = $tmp;

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/quoteRequest/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_quoteRequest_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function exportAction(Request $request, $dateStart, $dateEnd, $status)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('q'))
            ->from('PaprecCommercialBundle:QuoteRequest', 'q')
            ->where('q.deleted IS NULL');
        if ($status != null && !empty($status)) {
            $queryBuilder->andWhere('q.quoteStatus = :status')
                ->setParameter('status', $status);
        }
        if ($dateStart != null && $dateEnd != null && !empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder->andWhere('q.dateCreation BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        $quoteRequests = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Paprec Easy Recyclage - Devis")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Canton')
            ->setCellValue('D1', 'Civilité')
            ->setCellValue('E1', 'Nom')
            ->setCellValue('F1', 'Prénom')
            ->setCellValue('G1', 'Email')
            ->setCellValue('H1', 'Téléphone')
            ->setCellValue('I1', 'Adresse')
            ->setCellValue('J1', 'Code postal')
            ->setCellValue('K1', 'Ville')
            ->setCellValue('L1', 'Statut')
            ->setCellValue('M1', 'Comment. client')
            ->setCellValue('N1', 'Nb collab.')
            ->setCellValue('O1', 'Commercial en charge')
            ->setCellValue('P1', 'Bduget mensuel')
            ->setCellValue('Q1', 'Fréquence')
            ->setCellValue('R1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Devis');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($quoteRequests as $quoteRequest) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $quoteRequest->getId())
                ->setCellValue('B' . $i, $quoteRequest->getBusinessName())
                ->setCellValue('C' . $i, $quoteRequest->getCanton())
                ->setCellValue('D' . $i, $quoteRequest->getCivility())
                ->setCellValue('E' . $i, $quoteRequest->getLastName())
                ->setCellValue('F' . $i, $quoteRequest->getFirstName())
                ->setCellValue('G' . $i, $quoteRequest->getEmail())
                ->setCellValue('H' . $i, $quoteRequest->getPhone())
                ->setCellValue('I' . $i, $quoteRequest->getAddress())
                ->setCellValue('J' . $i, $quoteRequest->getPostalCode())
                ->setCellValue('K' . $i, $quoteRequest->getCity())
                ->setCellValue('L' . $i, $quoteRequest->getQuoteStatus())
                ->setCellValue('M' . $i, $quoteRequest->getComment())
                ->setCellValue('N' . $i, $quoteRequest->getCoworkerNumber())
                ->setCellValue('O' . $i, $quoteRequest->getUserInCharge()->getFirstName() . ' ' . $quoteRequest->getUserInCharge()->getLastName())
                ->setCellValue('P' . $i, $numberManager->denormalize($quoteRequest->getMonthlyBudget()))
                ->setCellValue('Q' . $i, $quoteRequest->getFrequency())
                ->setCellValue('R' . $i, $quoteRequest->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'ReisswolfShop-Extraction-Devis--' . date('Y-m-d') . '.xlsx';

        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/quoteRequest/view/{id}", name="paprec_commercial_quoteRequest_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, QuoteRequest $quoteRequest)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);

        return $this->render('PaprecCommercialBundle:QuoteRequest:view.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * @Route("/quoteRequest/add", name="paprec_commercial_quoteRequest_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();

        $numberManager = $this->get('paprec_catalog.number_manager');

        $quoteRequest = new QuoteRequest();

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $locales = array();
        foreach ($this->getParameter('paprec_languages') as $language) {
            $locales[$language] = strtolower($language);
        }

        $access = array();
        foreach ($this->getParameter('paprec_quote_access') as $a) {
            $access[$a] = $a;
        }

        $staff = array();
        foreach ($this->getParameter('paprec_quote_staff') as $s) {
            $staff[$s] = $s;
        }

        $form = $this->createForm(QuoteRequestType::class, $quoteRequest, array(
            'status' => $status,
            'locales' => $locales,
            'access' => $access,
            'staff' => $staff
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quoteRequest = $form->getData();

            $quoteRequest->setOverallDiscount($numberManager->normalize($quoteRequest->getOverallDiscount()));
            $quoteRequest->setMonthlyBudget($numberManager->normalize($quoteRequest->getMonthlyBudget()));

            $quoteRequest->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($quoteRequest);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:QuoteRequest:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/quoteRequest/edit/{id}", name="paprec_commercial_quoteRequest_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, QuoteRequest $quoteRequest)
    {
        $user = $this->getUser();

        $numberManager = $this->get('paprec_catalog.number_manager');
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $locales = array();
        foreach ($this->getParameter('paprec_languages') as $language) {
            $locales[$language] = strtolower($language);
        }

        $access = array();
        foreach ($this->getParameter('paprec_quote_access') as $a) {
            $access[$a] = $a;
        }

        $staff = array();
        foreach ($this->getParameter('paprec_quote_staff') as $s) {
            $staff[$s] = $s;
        }

        $quoteRequest->setOverallDiscount($numberManager->denormalize($quoteRequest->getOverallDiscount()));
        $quoteRequest->setMonthlyBudget($numberManager->denormalize($quoteRequest->getMonthlyBudget()));

        $form = $this->createForm(QuoteRequestType::class, $quoteRequest, array(
            'status' => $status,
            'locales' => $locales,
            'access' => $access,
            'staff' => $staff
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quoteRequest = $form->getData();
            $quoteRequest->setOverallDiscount($numberManager->normalize($quoteRequest->getOverallDiscount()));
            $quoteRequest->setMonthlyBudget($numberManager->normalize($quoteRequest->getMonthlyBudget()));

            $quoteRequest->setDateUpdate(new \DateTime());
            $quoteRequest->setUserUpdate($user);


            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:QuoteRequest:edit.html.twig', array(
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * @Route("/quoteRequest/remove/{id}", name="paprec_commercial_quoteRequest_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function removeAction(Request $request, QuoteRequest $quoteRequest)
    {
        $em = $this->getDoctrine()->getManager();

        $quoteRequest->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
    }

    /**
     * @Route("/quoteRequest/removeMany/{ids}", name="paprec_commercial_quoteRequest_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $quoteRequests = $em->getRepository('PaprecCommercialBundle:QuoteRequest')->findById($ids);
            foreach ($quoteRequests as $quoteRequest) {
                $quoteRequest->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
    }

    /**
     * @Route("/quoteRequest/{id}/addLine", name="paprec_commercial_quoteRequest_addLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function addLineAction(Request $request, QuoteRequest $quoteRequest)
    {

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $quoteRequestLine = new QuoteRequestLine();

        $form = $this->createForm(QuoteRequestLineAddType::class, $quoteRequestLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');

            $quoteRequestLine = $form->getData();
            $quoteRequestManager->addLine($quoteRequest, $quoteRequestLine, $user);

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:QuoteRequestLine:add.html.twig', array(
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest,
        ));
    }

    /**
     * @Route("/quoteRequest/{id}/editLine/{quoteLineId}", name="paprec_commercial_quoteRequest_editLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @ParamConverter("quoteRequest", options={"id" = "id"})
     * @ParamConverter("quoteRequestLine", options={"id" = "quoteLineId"})
     */
    public function editLineAction(Request $request, QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine)
    {
        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($quoteRequestLine->getQuoteRequest() !== $quoteRequest) {
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();

        $form = $this->createForm(QuoteRequestLineEditType::class, $quoteRequestLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');

            $quoteRequestManager->editLine($quoteRequest, $quoteRequestLine, $user);

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:QuoteRequestLine:edit.html.twig', array(
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest,
            'quoteRequestLine' => $quoteRequestLine
        ));
    }

    /**
     * @Route("/quoteRequest/{id}/removeLine/{quoteLineId}", name="paprec_commercial_quoteRequest_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @ParamConverter("quoteRequest", options={"id" = "id"})
     * @ParamConverter("quoteRequestLine", options={"id" = "quoteLineId"})
     */
    public function removeLineAction(Request $request, QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine)
    {
        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($quoteRequestLine->getQuoteRequest() !== $quoteRequest) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($quoteRequestLine);
        $em->flush();

        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $total = $quoteRequestManager->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
            'id' => $quoteRequest->getId()
        ));
    }

    /**
     * @Route("/quoteRequest/{id}/sendGeneratedQuote", name="paprec_commercial_quoteRequest_sendGeneratedQuote")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function sendGeneratedQuoteAction(QuoteRequest $quoteRequest)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);


        $sendQuote = $quoteRequestManager->sendGeneratedQuoteEmail($quoteRequest, $quoteRequest->getLocale());
        if ($sendQuote) {
            $this->get('session')->getFlashBag()->add('success', 'generatedQuoteSent');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'generatedQuoteNotSent');
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
            'id' => $quoteRequest->getId()
        ));
    }

}
