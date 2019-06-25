<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Form\BusinessLine\BusinessLineType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BusinessLineController extends Controller
{
    /**
     * @Route("/businessLine", name="paprec_commercial_businessLine_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:BusinessLine:index.html.twig');
    }

    /**
     * @Route("/businessLine/loadList", name="paprec_commercial_businessLine_loadList")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function loadListAction(Request $request)
    {
        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'b.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'b.name', 'method' => array('getName'));
        $cols['division'] = array('label' => 'division', 'id' => 'b.division', 'method' => array('getDivision'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'b.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('b'))
            ->from('PaprecCommercialBundle:BusinessLine', 'b')
            ->where('b.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('b.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('b.name', '?1'),
                    $queryBuilder->expr()->like('b.division', '?1'),
                    $queryBuilder->expr()->like('b.division', '?1'),
                    $queryBuilder->expr()->like('b.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/businessLine/export", name="paprec_commercial_businessLine_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('b'))
            ->from('PaprecCommercialBundle:BusinessLine', 'b')
            ->where('b.deleted IS NULL')
        ;

        $businessLines = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Secteurs d'activités")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Name')
            ->setCellValue('C1', 'Division')
            ->setCellValue('D1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Secteurs d\'activités');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach($businessLines as $businessLine) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $businessLine->getId())
                ->setCellValue('B'.$i, $businessLine->getName())
                ->setCellValue('C'.$i, $businessLine->getDivision())
                ->setCellValue('D'.$i, $businessLine->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Secteurs-Activites-'.date('Y-m-d').'.xlsx';

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
     * @Route("/businessLine/view/{id}", name="paprec_commercial_businessLine_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, BusinessLine $businessLine)
    {
        $businessLineManager = $this->get('paprec_commercial.business_line_manager');
        $businessLineManager->isDeleted($businessLine, true);

        return $this->render('PaprecCommercialBundle:BusinessLine:view.html.twig', array(
            'businessLine' => $businessLine
        ));
    }

    /**
     * @Route("/businessLine/add", name="paprec_commercial_businessLine_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function addAction(Request $request)
    {

        $businessLine = new BusinessLine();

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(BusinessLineType::class, $businessLine, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $businessLine = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($businessLine);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_businessLine_view', array(
                'id' => $businessLine->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:BusinessLine:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/businessLine/edit/{id}", name="paprec_commercial_businessLine_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editAction(Request $request, BusinessLine $businessLine)
    {
        $businessLineManager = $this->get('paprec_commercial.business_line_manager');
        $businessLineManager->isDeleted($businessLine, true);

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(BusinessLineType::class, $businessLine, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $businessLine = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_businessLine_view', array(
                'id' => $businessLine->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:BusinessLine:edit.html.twig', array(
            'form' => $form->createView(),
            'businessLine' => $businessLine
        ));
    }

    /**
     * @Route("/businessLine/remove/{id}", name="paprec_commercial_businessLine_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function removeAction(Request $request, BusinessLine $businessLine)
    {
        $em = $this->getDoctrine()->getManager();

        $businessLine->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_businessLine_index');
    }

    /**
     * @Route("/businessLine/removeMany/{ids}", name="paprec_commercial_businessLine_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if(! $ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if(is_array($ids) && count($ids)) {
            $businessLines = $em->getRepository('PaprecCommercialBundle:BusinessLine')->findById($ids);
            foreach ($businessLines as $businessLine){
                $businessLine->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_businessLine_index');
    }


}
