<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\ProductDIQuote;
use Paprec\CommercialBundle\Entity\ProductDIQuoteLine;
use Paprec\CommercialBundle\Form\ProductDIQuote\ProductDIQuoteLineAddType;
use Paprec\CommercialBundle\Form\ProductDIQuote\ProductDIQuoteLineEditType;
use Paprec\CommercialBundle\Form\ProductDIQuote\ProductDIQuoteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDIQuoteController extends Controller
{

    /**
     * @Route("/productDIQuote", name="paprec_commercial_productDIQuote_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductDIQuote:index.html.twig');
    }

    /**
     * @Route("/productDIQuote/loadList", name="paprec_commercial_productDIQuote_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'p.businessName', 'method' => array('getBusinessName'));
        $cols['totalAmount'] = array('label' => 'totalAmount', 'id' => 'p.totalAmount', 'method' => array('getTotalAmount'));
        $cols['quoteStatus'] = array('label' => 'quoteStatus', 'id' => 'p.quoteStatus', 'method' => array('getQuoteStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'p.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductDIQuote', 'p')
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
     * @Route("/productDIQuote/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_productDIQuote_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function exportAction(Request $request, $dateStart, $dateEnd, $status)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductDIQuote', 'p')
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

        $productDIQuotes = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Devis DI")
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

        $phpExcelObject->getActiveSheet()->setTitle('Devis DI');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productDIQuotes as $productDIQuote) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productDIQuote->getId())
                ->setCellValue('B' . $i, $productDIQuote->getBusinessName())
                ->setCellValue('C' . $i, $productDIQuote->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productDIQuote->getCivility())
                ->setCellValue('E' . $i, $productDIQuote->getLastName())
                ->setCellValue('F' . $i, $productDIQuote->getFirstName())
                ->setCellValue('G' . $i, $productDIQuote->getEmail())
                ->setCellValue('H' . $i, $productDIQuote->getAddress())
                ->setCellValue('I' . $i, $productDIQuote->getPostalCode())
                ->setCellValue('J' . $i, $productDIQuote->getCity())
                ->setCellValue('K' . $i, $productDIQuote->getPhone())
                ->setCellValue('L' . $i, $productDIQuote->getQuoteStatus())
                ->setCellValue('M' . $i, $numberManager->denormalize($productDIQuote->getTotalAmount()))
                ->setCellValue('N' . $i, $numberManager->denormalize($productDIQuote->getGeneratedTurnover()))
                ->setCellValue('O' . $i, $productDIQuote->getAgency())
                ->setCellValue('P' . $i, $productDIQuote->getSummary())
                ->setCellValue('Q' . $i, $productDIQuote->getFrequency())
                ->setCellValue('R' . $i, $productDIQuote->getTonnage())
                ->setCellValue('S' . $i, $productDIQuote->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Devis-DI-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productDIQuote/view/{id}", name="paprec_commercial_productDIQuote_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, ProductDIQuote $productDIQuote)
    {
        $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');
        $productDIQuoteManager->isDeleted($productDIQuote, true);

        return $this->render('PaprecCommercialBundle:ProductDIQuote:view.html.twig', array(
            'productDIQuote' => $productDIQuote
        ));
    }

    /**
     * @Route("/productDIQuote/add", name="paprec_commercial_productDIQuote_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productDIQuote = new ProductDIQuote();

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ProductDIQuoteType::class, $productDIQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDIQuote = $form->getData();
            $productDIQuote->setGeneratedTurnover($numberManager->normalize($productDIQuote->getGeneratedTurnover()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($productDIQuote);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
                'id' => $productDIQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIQuote:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productDIQuote/edit/{id}", name="paprec_commercial_productDIQuote_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, ProductDIQuote $productDIQuote)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');
        $productDIQuoteManager->isDeleted($productDIQuote, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $productDIQuote->setGeneratedTurnover($numberManager->denormalize($productDIQuote->getGeneratedTurnover()));


        $form = $this->createForm(ProductDIQuoteType::class, $productDIQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDIQuote = $form->getData();
            $productDIQuote->setGeneratedTurnover($numberManager->normalize($productDIQuote->getGeneratedTurnover()));

            $productDIQuote->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
                'id' => $productDIQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIQuote:edit.html.twig', array(
            'form' => $form->createView(),
            'productDIQuote' => $productDIQuote
        ));
    }

    /**
     * @Route("/productDIQuote/remove/{id}", name="paprec_commercial_productDIQuote_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function removeAction(Request $request, ProductDIQuote $productDIQuote)
    {
        $em = $this->getDoctrine()->getManager();

        $productDIQuote->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productDIQuote_index');
    }

    /**
     * @Route("/productDIQuote/removeMany/{ids}", name="paprec_commercial_productDIQuote_removeMany")
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
            $productDIQuotes = $em->getRepository('PaprecCommercialBundle:ProductDIQuote')->findById($ids);
            foreach ($productDIQuotes as $productDIQuote) {
                $productDIQuote->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productDIQuote_index');
    }

    /**
     * @Route("/productDIQuote/{id}/addLine", name="paprec_commercial_productDIQuote_addLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     */
    public function addLineAction(Request $request, ProductDIQuote $productDIQuote)
    {

        $em = $this->getDoctrine()->getManager();
        $selectedProductId = $request->get('selectedProductId');
        $submitForm = $request->get('submitForm');

        if ($productDIQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productDIQuoteLine = new ProductDIQuoteLine();

        $form = $this->createForm(ProductDIQuoteLineAddType::class, $productDIQuoteLine,
            array(
                'selectedProductId' => $selectedProductId
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $submitForm) {
            $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');

            $productDIQuoteLine = $form->getData();
            $productDIQuoteManager->addLine($productDIQuote, $productDIQuoteLine);

            return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
                'id' => $productDIQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIQuoteLine:add.html.twig', array(
            'form' => $form->createView(),
            'productDIQuote' => $productDIQuote,
        ));
    }

    /**
     * @Route("/productDIQuote/{id}/editLine/{quoteLineId}", name="paprec_commercial_productDIQuote_editLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @ParamConverter("productDIQuote", options={"id" = "id"})
     * @ParamConverter("productDIQuoteLine", options={"id" = "quoteLineId"})
     */
    public function editLineAction(Request $request, ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {
        if ($productDIQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productDIQuoteLine->getProductDIQuote() !== $productDIQuote) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductDIQuoteLineEditType::class, $productDIQuoteLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');

            $productDIQuoteManager->editLine($productDIQuote, $productDIQuoteLine);

            return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
                'id' => $productDIQuote->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductDIQuoteLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productDIQuote' => $productDIQuote,
            'productDIQuoteLine' => $productDIQuoteLine
        ));
    }

    /**
     * @Route("/productDIQuote/{id}/removeLine/{quoteLineId}", name="paprec_commercial_productDIQuote_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @ParamConverter("productDIQuote", options={"id" = "id"})
     * @ParamConverter("productDIQuoteLine", options={"id" = "quoteLineId"})
     */
    public function removeLineAction(Request $request, ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {
        if ($productDIQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productDIQuoteLine->getProductDIQuote() !== $productDIQuote) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productDIQuoteLine);
        $em->flush();

        $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');
        $total = $productDIQuoteManager->calculateTotal($productDIQuote);
        $productDIQuote->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
            'id' => $productDIQuote->getId()
        ));
    }

    /**
     * @Route("/productDIQuote/{id}/sendGeneratedQuote", name="paprec_commercial_productDIQuote_sendGeneratedQuote")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function sendGeneratedQuoteAction(ProductDIQuote $productDIQuote)
    {
        $productDIQuoteManager = $this->get('paprec_commercial.product_di_quote_manager');
        $productDIQuoteManager->isDeleted($productDIQuote, true);


        $sendQuote = $productDIQuoteManager->sendGeneratedQuoteEmail($productDIQuote);
        if ($sendQuote) {
            $this->get('session')->getFlashBag()->add('success', 'generatedQuoteSent');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'generatedQuoteNotSent');
        }

        return $this->redirectToRoute('paprec_commercial_productDIQuote_view', array(
            'id' => $productDIQuote->getId()
        ));
    }

}
