<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Entity\ProductD3EQuoteLine;
use Paprec\CommercialBundle\Form\ProductD3EQuote\ProductD3EQuoteLineAddType;
use Paprec\CommercialBundle\Form\ProductD3EQuote\ProductD3EQuoteLineEditType;
use Paprec\CommercialBundle\Form\ProductD3EQuote\ProductD3EQuoteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductD3EQuoteController extends Controller
{

    /**
     * @Route("/productD3EQuote", name="paprec_commercial_productD3EQuote_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductD3EQuote:index.html.twig');
    }

    /**
     * @Route("/productD3EQuote/loadList", name="paprec_commercial_productD3EQuote_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
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
            ->from('PaprecCommercialBundle:ProductD3EQuote', 'p')
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
     * @Route("/productD3EQuote/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_productD3EQuote_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function exportAction(Request $request, $dateStart, $dateEnd, $status)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductD3EQuote', 'p')
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

        $productD3EQuotes = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Devis D3E")
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

        $phpExcelObject->getActiveSheet()->setTitle('Devis D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productD3EQuotes as $productD3EQuote) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productD3EQuote->getId())
                ->setCellValue('B' . $i, $productD3EQuote->getBusinessName())
                ->setCellValue('C' . $i, $productD3EQuote->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productD3EQuote->getCivility())
                ->setCellValue('E' . $i, $productD3EQuote->getLastName())
                ->setCellValue('F' . $i, $productD3EQuote->getFirstName())
                ->setCellValue('G' . $i, $productD3EQuote->getEmail())
                ->setCellValue('H' . $i, $productD3EQuote->getAddress())
                ->setCellValue('I' . $i, $productD3EQuote->getPostalCode())
                ->setCellValue('J' . $i, $productD3EQuote->getCity())
                ->setCellValue('K' . $i, $productD3EQuote->getPhone())
                ->setCellValue('L' . $i, $productD3EQuote->getQuoteStatus())
                ->setCellValue('M' . $i, $numberManager->denormalize($productD3EQuote->getTotalAmount()))
                ->setCellValue('N' . $i, $numberManager->denormalize($productD3EQuote->getGeneratedTurnover()))
                ->setCellValue('O' . $i, $productD3EQuote->getAgency())
                ->setCellValue('P' . $i, $productD3EQuote->getSummary())
                ->setCellValue('Q' . $i, $productD3EQuote->getFrequency())
                ->setCellValue('R' . $i, $productD3EQuote->getTonnage())
                ->setCellValue('S' . $i, $productD3EQuote->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Devis-D3E-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productD3EQuote/view/{id}", name="paprec_commercial_productD3EQuote_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function viewAction(Request $request, ProductD3EQuote $productD3EQuote)
    {
        $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');
        $productD3EQuoteManager->isDeleted($productD3EQuote, true);

        return $this->render('PaprecCommercialBundle:ProductD3EQuote:view.html.twig', array(
            'productD3EQuote' => $productD3EQuote
        ));
    }

    /**
     * @Route("/productD3EQuote/add", name="paprec_commercial_productD3EQuote_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productD3EQuote = new ProductD3EQuote();

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ProductD3EQuoteType::class, $productD3EQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3EQuote = $form->getData();
            $productD3EQuote->setGeneratedTurnover($numberManager->normalize($productD3EQuote->getGeneratedTurnover()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($productD3EQuote);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
                'id' => $productD3EQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductD3EQuote:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3EQuote/edit/{id}", name="paprec_commercial_productD3EQuote_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, ProductD3EQuote $productD3EQuote)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');
        $productD3EQuoteManager->isDeleted($productD3EQuote, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $productD3EQuote->setGeneratedTurnover($numberManager->denormalize($productD3EQuote->getGeneratedTurnover()));

        $form = $this->createForm(ProductD3EQuoteType::class, $productD3EQuote, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3EQuote = $form->getData();
            $productD3EQuote->setGeneratedTurnover($numberManager->normalize($productD3EQuote->getGeneratedTurnover()));

            $productD3EQuote->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
                'id' => $productD3EQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductD3EQuote:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3EQuote' => $productD3EQuote
        ));
    }

    /**
     * @Route("/productD3EQuote/remove/{id}", name="paprec_commercial_productD3EQuote_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removeAction(Request $request, ProductD3EQuote $productD3EQuote)
    {
        $em = $this->getDoctrine()->getManager();

        $productD3EQuote->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productD3EQuote_index');
    }

    /**
     * @Route("/productD3EQuote/removeMany/{ids}", name="paprec_commercial_productD3EQuote_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
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
            $productD3EQuotes = $em->getRepository('PaprecCommercialBundle:ProductD3EQuote')->findById($ids);
            foreach ($productD3EQuotes as $productD3EQuote) {
                $productD3EQuote->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productD3EQuote_index');
    }

    /**
     * @Route("/productD3EQuote/{id}/addLine", name="paprec_commercial_productD3EQuote_addLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function addLineAction(Request $request, ProductD3EQuote $productD3EQuote)
    {

        $em = $this->getDoctrine()->getManager();
        $selectedProductId = $request->get('selectedProductId');
        $submitForm = $request->get('submitForm');


        if ($productD3EQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productD3EQuoteLine = new ProductD3EQuoteLine();

        $form = $this->createForm(ProductD3EQuoteLineAddType::class, $productD3EQuoteLine,
            array(
                'selectedProductId' => $selectedProductId
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $submitForm) {
            $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');

            $productD3EQuoteLine = $form->getData();
            $productD3EQuoteManager->addLine($productD3EQuote, $productD3EQuoteLine);
            return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
                'id' => $productD3EQuote->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductD3EQuoteLine:add.html.twig', array(
            'form' => $form->createView(),
            'productD3EQuote' => $productD3EQuote,
        ));
    }

    /**
     * @Route("/productD3EQuote/{id}/editLine/{quoteLineId}", name="paprec_commercial_productD3EQuote_editLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @ParamConverter("productD3EQuote", options={"id" = "id"})
     * @ParamConverter("productD3EQuoteLine", options={"id" = "quoteLineId"})
     */
    public function editLineAction(Request $request, ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        if ($productD3EQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EQuoteLine->getProductD3EQuote() !== $productD3EQuote) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductD3EQuoteLineEditType::class, $productD3EQuoteLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');

            $productD3EQuoteManager->editLine($productD3EQuote, $productD3EQuoteLine);

            return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
                'id' => $productD3EQuote->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductD3EQuoteLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3EQuote' => $productD3EQuote,
            'productD3EQuoteLine' => $productD3EQuoteLine
        ));
    }

    /**
     * @Route("/productD3EQuote/{id}/removeLine/{quoteLineId}", name="paprec_commercial_productD3EQuote_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @ParamConverter("productD3EQuote", options={"id" = "id"})
     * @ParamConverter("productD3EQuoteLine", options={"id" = "quoteLineId"})
     */
    public function removeLineAction(Request $request, ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        if ($productD3EQuote->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EQuoteLine->getProductD3EQuote() !== $productD3EQuote) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productD3EQuoteLine);
        $em->flush();

        $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');
        $total = $productD3EQuoteManager->calculateTotal($productD3EQuote);
        $productD3EQuote->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
            'id' => $productD3EQuote->getId()
        ));
    }

    /**
     * @Route("/productD3EQuote/{id}/sendGeneratedQuote", name="paprec_commercial_productD3EQuote_sendGeneratedQuote")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function sendGeneratedQuoteAction(ProductD3EQuote $productD3EQuote)
    {
        $productD3EQuoteManager = $this->get('paprec_commercial.product_d3e_quote_manager');
        $productD3EQuoteManager->isDeleted($productD3EQuote, true);


        $sendQuote = $productD3EQuoteManager->sendGeneratedQuoteEmail($productD3EQuote);
        if ($sendQuote) {
            $this->get('session')->getFlashBag()->add('success', 'generatedQuoteSent');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'generatedQuoteNotSent');
        }

        return $this->redirectToRoute('paprec_commercial_productD3EQuote_view', array(
            'id' => $productD3EQuote->getId()
        ));
    }
}
