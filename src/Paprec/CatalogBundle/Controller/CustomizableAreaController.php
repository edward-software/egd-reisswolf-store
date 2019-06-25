<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\CustomizableArea;
use Paprec\CatalogBundle\Form\CustomizableAreaEditType;
use Paprec\CatalogBundle\Form\CustomizableAreaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomizableAreaController extends Controller
{
    /**
     * @Route("/customizableArea",  name="paprec_catalog_customizableArea_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:CustomizableArea:index.html.twig');
    }

    /**
     * @Route("/customizableArea/loadList",  name="paprec_catalog_customizableArea_loadList")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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

        $cols['id'] = array('label' => 'id', 'id' => 'c.id', 'method' => array('getId'));
        $cols['code'] = array('label' => 'code', 'id' => 'c.code', 'method' => array('getCode'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'c.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:CustomizableArea', 'c')
            ->where('c.deleted IS NULL')
        ;

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('c.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('c.code', '?1'),
                    $queryBuilder->expr()->like('c.dateCreation', '?1')
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
     * @Route("/customizableArea/export",  name="paprec_catalog_customizableArea_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction()
    {
        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:CustomizableArea', 'c')
            ->where('c.deleted IS NULL');

        $customizableAreas = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Zones personnalisables")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Code')
            ->setCellValue('C1', 'Contenu')
            ->setCellValue('D1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Zones personalisables');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach($customizableAreas as $customizableArea) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $customizableArea->getId())
                ->setCellValue('B'.$i, $customizableArea->getCode())
                ->setCellValue('C'.$i, $customizableArea->getContent())
                ->setCellValue('D'.$i, $customizableArea->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Zones-Personnalisables-'.date('Y-m-d').'.xlsx';

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
     * @Route("/customizableArea/view/{id}",  name="paprec_catalog_customizableArea_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function viewAction(Request $request, CustomizableArea $customizableArea)
    {
        $customizableAreaManager = $this->get('paprec_catalog.customizable_area_manager');
        $customizableAreaManager->isDeleted($customizableArea, true);

        if($customizableArea->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        return $this->render('PaprecCatalogBundle:CustomizableArea:view.html.twig', array(
            'customizableArea' => $customizableArea
        ));
    }

    /**
     * @Route("/customizableArea/add",  name="paprec_catalog_customizableArea_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $customizableArea = new CustomizableArea();
        $customizableAreaManager = $this->get('paprec_catalog.customizable_area_manager');

        $codes = $customizableAreaManager->getUnallocated();

        $form = $this->createForm(CustomizableAreaType::class, $customizableArea, array(
            'codes' => $codes
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customizableArea = $form->getData();
            $customizableArea->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($customizableArea);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_customizableArea_view', array(
                'id' => $customizableArea->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:CustomizableArea:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/customizableArea/edit/{id}",  name="paprec_catalog_customizableArea_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Exception
     */
    public function editAction(Request $request, CustomizableArea $customizableArea)
    {
        $customizableAreaManager = $this->get('paprec_catalog.customizable_area_manager');
        $customizableAreaManager->isDeleted($customizableArea, true);

        $form = $this->createForm(CustomizableAreaEditType::class, $customizableArea);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $customizableArea = $form->getData();
            $customizableArea->setDateUpdate(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_customizableArea_view', array(
                'id' => $customizableArea->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:CustomizableArea:edit.html.twig', array(
            'form' => $form->createView(),
            'customizableArea' => $customizableArea
        ));
    }

    /**
     * @Route("/customizableArea/remove/{id}", name="paprec_catalog_customizableArea_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Exception
     */
    public function removeAction(Request $request, CustomizableArea $customizableArea)
    {
        $em = $this->getDoctrine()->getManager();

        $customizableArea->setDeleted(new \DateTime());

        $em->flush();

        return $this->redirectToRoute('paprec_catalog_customizableArea_index');
    }

    /**
     * @Route("/customizableArea/removeMany/{ids}", name="paprec_catalog_customizableArea_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Exception
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
            $customizableAreas = $em->getRepository('PaprecCatalogBundle:CustomizableArea')->findById($ids);
            foreach ($customizableAreas as $customizableArea){
                $customizableArea->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_customizableArea_index');
    }
}
