<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Entity\ProductChantierQuoteLine;
use Paprec\CommercialBundle\Form\ProductChantierQuote\ProductChantierQuoteLineAddType;
use Paprec\CommercialBundle\Form\ProductChantierQuote\ProductChantierQuoteLineEditType;
use Paprec\CommercialBundle\Form\ProductChantierQuote\ProductChantierQuoteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductChantierQuoteController extends Controller
{

    /**
     * @Route("/productChantierQuote", name="paprec_commercial_productChantierQuote_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductChantierQuote:index.html.twig');
    }

    /**
     * @Route("/productChantierQuote/loadList", name="paprec_commercial_productChantierQuote_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
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

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'p.businessName', 'method' => array('getBusinessName'));
        $cols['totalAmount'] = array('label' => 'totalAmount', 'id' => 'p.totalAmount', 'method' => array('getTotalAmount'));
        $cols['quoteStatus'] = array('label' => 'quoteStatus', 'id' => 'p.quoteStatus', 'method' => array('getQuoteStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'p.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductChantierQuote', 'p')
            ->where('p.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.businessName', '?1'),
                    $queryBuilder->expr()->like('p.totalAmount', '?1'),
                    $queryBuilder->expr()->like('p.quoteStatus', '?1'),
                    $queryBuilder->expr()->like('p.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
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
     * @Route("/productChantierQuote/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_productChantierQuote_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function exportAction(Request $request, $dateStart, $dateEnd, $status)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductChantierQuote', 'p')
            ->where('p.deleted IS NULL');
        if ($status != null && !empty($status)) {
            $queryBuilder->andWhere('p.quoteStatus = :status')
                ->setParameter('status', $status);
        }
        if ($dateStart != null && $dateEnd != null && !empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder->andWhere('p.dateCreation BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        $productChantierQuotes = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Devis Chantier")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Secteur d\'activité')
            ->setCellValue('D1', 'Civilité')
            ->setCellValue('E1', 'Nom')
            ->setCellValue('F1', 'Prénom')
            ->setCellValue('G1', 'Email')
            ->setCellValue('H1', 'Adresse')
            ->setCellValue('I1', 'Code postal')
            ->setCellValue('J1', 'Ville')
            ->setCellValue('K1', 'Téléphone')
            ->setCellValue('L1', 'Statut')
            ->setCellValue('M1', 'Montant total')
            ->setCellValue('N1', 'CA généré')
            ->setCellValue('O1', 'Agence associée')
            ->setCellValue('P1', 'Résumé du besoin')
            ->setCellValue('Q1', 'Fréquence')
            ->setCellValue('R1', 'Tonnage')
            ->setCellValue('S1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Devis Chantier');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productChantierQuotes as $productChantierQuote) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productChantierQuote->getId())
                ->setCellValue('B' . $i, $productChantierQuote->getBusinessName())
                ->setCellValue('C' . $i, $productChantierQuote->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productChantierQuote->getCivility())
                ->setCellValue('E' . $i, $productChantierQuote->getLastName())
                ->setCellValue('F' . $i, $productChantierQuote->getFirstName())
                ->setCellValue('G' . $i, $productChantierQuote->getEmail())
                ->setCellValue('H' . $i, $productChantierQuote->getAddress())
                ->setCellValue('I' . $i, $productChantierQuote->getPostalCode())
                ->setCellValue('J' . $i, $productChantierQuote->getCity())
                ->setCellValue('K' . $i, $productChantierQuote->getPhone())
                ->setCellValue('L' . $i, $productChantierQuote->getQuoteStatus())
                ->setCellValue('M' . $i, $numberManager->denormalize($productChantierQuote->getTotalAmount()))
                ->setCellValue('N' . $i, $numberManager->denormalize($productChantierQuote->getGeneratedTurnover()))
                ->setCellValue('O' . $i, $productChantierQuote->getAgency())
                ->setCellValue('P' . $i, $productChantierQuote->getSummary())
                ->setCellValue('Q' . $i, $productChantierQuote->getFrequency())
                ->setCellValue('R' . $i, $productChantierQuote->getTonnage())
                ->setCellValue('S' . $i, $productChantierQuote->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Devis-Chantier-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productChantierQuote/view/{id}", name="paprec_commercial_productChantierQuote_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function viewAction(Request $request, ProductChantierQuote $productChantierQuote)
    {
        $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');
        $productChantierQuoteManager->isDeleted($productChantierQuote, true);

        return $this->render('PaprecCommercialBundle:ProductChantierQuote:view.html.twig', array(
            'productChantierQuote' => $productChantierQuote
        ));
    }

    /**
     * @Route("/productChantierQuote/add", name="paprec_commercial_productChantierQuote_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productChantierQuote = new ProductChantierQuote();

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ProductChantierQuoteType::class, $productChantierQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierQuote = $form->getData();
            $productChantierQuote->setGeneratedTurnover($numberManager->normalize($productChantierQuote->getGeneratedTurnover()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($productChantierQuote);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
                'id' => $productChantierQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductChantierQuote:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productChantierQuote/edit/{id}", name="paprec_commercial_productChantierQuote_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, ProductChantierQuote $productChantierQuote)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');
        $productChantierQuoteManager->isDeleted($productChantierQuote, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $productChantierQuote->setGeneratedTurnover($numberManager->denormalize($productChantierQuote->getGeneratedTurnover()));

        $form = $this->createForm(ProductChantierQuoteType::class, $productChantierQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierQuote = $form->getData();
            $productChantierQuote->setGeneratedTurnover($numberManager->normalize($productChantierQuote->getGeneratedTurnover()));

            $productChantierQuote->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
                'id' => $productChantierQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductChantierQuote:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantierQuote' => $productChantierQuote
        ));
    }

    /**
     * @Route("/productChantierQuote/remove/{id}", name="paprec_commercial_productChantierQuote_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function removeAction(Request $request, ProductChantierQuote $productChantierQuote)
    {
        $em = $this->getDoctrine()->getManager();

        $productChantierQuote->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productChantierQuote_index');
    }

    /**
     * @Route("/productChantierQuote/removeMany/{ids}", name="paprec_commercial_productChantierQuote_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
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
            $productChantierQuotes = $em->getRepository('PaprecCommercialBundle:ProductChantierQuote')->findById($ids);
            foreach ($productChantierQuotes as $productChantierQuote) {
                $productChantierQuote->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productChantierQuote_index');
    }

    /**
     * @Route("/productChantierQuote/{id}/addLine", name="paprec_commercial_productChantierQuote_addLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     */
    public function addLineAction(Request $request, ProductChantierQuote $productChantierQuote)
    {

        $em = $this->getDoctrine()->getManager();
        $selectedProductId = $request->get('selectedProductId');
        $submitForm = $request->get('submitForm');

        if ($productChantierQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productChantierQuoteLine = new ProductChantierQuoteLine();

        $form = $this->createForm(ProductChantierQuoteLineAddType::class, $productChantierQuoteLine,
            array(
                'selectedProductId' => $selectedProductId
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $submitForm) {
            $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');

            $productChantierQuoteLine = $form->getData();
            $productChantierQuoteManager->addLine($productChantierQuote, $productChantierQuoteLine);

            return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
                'id' => $productChantierQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductChantierQuoteLine:add.html.twig', array(
            'form' => $form->createView(),
            'productChantierQuote' => $productChantierQuote,
        ));
    }

    /**
     * @Route("/productChantierQuote/{id}/editLine/{quoteLineId}", name="paprec_commercial_productChantierQuote_editLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @ParamConverter("productChantierQuote", options={"id" = "id"})
     * @ParamConverter("productChantierQuoteLine", options={"id" = "quoteLineId"})
     */
    public function editLineAction(Request $request, ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        if ($productChantierQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierQuoteLine->getProductChantierQuote() !== $productChantierQuote) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductChantierQuoteLineEditType::class, $productChantierQuoteLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');

            $productChantierQuoteManager->editLine($productChantierQuote, $productChantierQuoteLine);

            return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
                'id' => $productChantierQuote->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductChantierQuoteLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantierQuote' => $productChantierQuote,
            'productChantierQuoteLine' => $productChantierQuoteLine
        ));
    }

    /**
     * @Route("/productChantierQuote/{id}/removeLine/{quoteLineId}", name="paprec_commercial_productChantierQuote_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @ParamConverter("productChantierQuote", options={"id" = "id"})
     * @ParamConverter("productChantierQuoteLine", options={"id" = "quoteLineId"})
     */
    public function removeLineAction(Request $request, ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        if ($productChantierQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierQuoteLine->getProductChantierQuote() !== $productChantierQuote) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productChantierQuoteLine);
        $em->flush();

        $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');
        $total = $productChantierQuoteManager->calculateTotal($productChantierQuote);
        $productChantierQuote->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
            'id' => $productChantierQuote->getId()
        ));
    }

    /**
     * @Route("/productChantierQuote/{id}/sendGeneratedQuote", name="paprec_commercial_productChantierQuote_sendGeneratedQuote")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'CHANTIER' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function sendGeneratedQuoteAction(ProductChantierQuote $productChantierQuote)
    {
        $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');
        $productChantierQuoteManager->isDeleted($productChantierQuote, true);


        $sendQuote = $productChantierQuoteManager->sendGeneratedQuoteEmail($productChantierQuote);
        if ($sendQuote) {
            $this->get('session')->getFlashBag()->add('success', 'generatedQuoteSent');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'generatedQuoteNotSent');
        }

        return $this->redirectToRoute('paprec_commercial_productChantierQuote_view', array(
            'id' => $productChantierQuote->getId()
        ));
    }
}
