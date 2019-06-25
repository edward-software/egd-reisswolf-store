<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Type;
use Paprec\CatalogBundle\Form\TypeType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TypeController extends Controller
{
    /**
     * @Route("/type",  name="paprec_catalog_type_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Type:index.html.twig');
    }

    /**
     * @Route("/type/loadList",  name="paprec_catalog_type_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'a.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'a.name', 'method' => array('getName'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'a.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCatalogBundle:Type', 'a')
            ->where('a.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('a.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('a.name', '?1'),
                    $queryBuilder->expr()->like('a.dateCreation', '?1')
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
     * @Route("/type/export",  name="paprec_catalog_type_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction()
    {
        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCatalogBundle:Type', 'a')
            ->where('a.deleted IS NULL');

        $types = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Types")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Types');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($types as $type) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $type->getId())
                ->setCellValue('B' . $i, $type->getName())
                ->setCellValue('C' . $i, $type->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Types-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/type/view/{id}",  name="paprec_catalog_type_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, Type $type)
    {
        $typeManager = $this->get('paprec_catalog.type_manager');
        $typeManager->isDeleted($type, true);

        return $this->render('PaprecCatalogBundle:Type:view.html.twig', array(
            'type' => $type
        ));
    }

    /**
     * @Route("/type/add",  name="paprec_catalog_type_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function addAction(Request $request)
    {
        $type = new Type();

        $form = $this->createForm(TypeType::class, $type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->getData();
            $type->setDateCreation(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->persist($type);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_type_view', array(
                'id' => $type->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Type:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/type/edit/{id}",  name="paprec_catalog_type_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, Type $type)
    {
        $typeManager = $this->get('paprec_catalog.type_manager');
        $typeManager->isDeleted($type, true);

        $form = $this->createForm(TypeType::class, $type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $type = $form->getData();
            $type->setDateUpdate(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_type_view', array(
                'id' => $type->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Type:edit.html.twig', array(
            'form' => $form->createView(),
            'type' => $type
        ));
    }

    /**
     * @Route("/type/remove/{id}", name="paprec_catalog_type_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function removeAction(Request $request, Type $type)
    {
        $em = $this->getDoctrine()->getManager();
        $type->setDeleted(new \DateTime);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_type_index');
    }


    /**
     * @Route("/type/removeMany/{ids}", name="paprec_catalog_type_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
            $types = $em->getRepository('PaprecCatalogBundle:Type')->findById($ids);
            foreach ($types as $type) {

                $type->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_type_index');
    }
}
