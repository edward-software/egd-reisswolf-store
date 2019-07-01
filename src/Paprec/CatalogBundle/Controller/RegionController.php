<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Region;
use Paprec\CatalogBundle\Form\RegionType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegionController extends Controller
{

    /**
     * @Route("/region", name="paprec_catalog_region_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Region:index.html.twig');
    }

    /**
     * @Route("/region/loadList", name="paprec_catalog_region_loadList")
     * @Security("has_role('ROLE_ADMIN')")
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

        $cols['id'] = array('label' => 'id', 'id' => 'r.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'r.name', 'method' => array('getName'));

        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(Region::class)->createQueryBuilder('r');


        $queryBuilder->select(array('r'))
            ->where('r.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('r.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('r.name', '?1')
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
     * @Route("/region/export", name="paprec_catalog_region_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(Region::class)->createQueryBuilder('pC');

        $queryBuilder->select(array('pC'))
            ->where('pC.deleted IS NULL');

        $regions = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Codes Postaux")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Codes')
            ->setCellValue('C1', 'Division')
            ->setCellValue('D1', 'Coef. Mult.');

        $phpExcelObject->getActiveSheet()->setTitle('Codes Postaux');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($regions as $region) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $region->getId())
                ->setCellValue('B' . $i, $region->getCodes())
                ->setCellValue('C' . $i, $region->getDivision())
                ->setCellValue('D' . $i, $numberManager->denormalize($region->getRate()));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'ReisswolfShop-Extraction-Codes-Postaux-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/region/view/{id}", name="paprec_catalog_region_view")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Region $region
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, Region $region)
    {
        $regionManager = $this->get('paprec_catalog.region_manager');
        $regionManager->isDeleted($region, true);

        return $this->render('PaprecCatalogBundle:Region:view.html.twig', array(
            'region' => $region
        ));
    }

    /**
     * @Route("/region/add", name="paprec_catalog_region_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();

        $region = new Region();

        $form = $this->createForm(RegionType::class, $region);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $region = $form->getData();

            $region->setDateCreation(new \DateTime);
            $region->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($region);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_region_view', array(
                'id' => $region->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Region:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/region/edit/{id}", name="paprec_catalog_region_edit")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Region $region
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editAction(Request $request, Region $region)
    {
        $user = $this->getUser();

        $regionManager = $this->get('paprec_catalog.region_manager');
        $regionManager->isDeleted($region, true);

        $form = $this->createForm(RegionType::class, $region);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $region = $form->getData();

            $region->setDateUpdate(new \DateTime);
            $region->setUserUpdate($user);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_region_view', array(
                'id' => $region->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Region:edit.html.twig', array(
            'form' => $form->createView(),
            'region' => $region
        ));
    }

    /**
     * @Route("/region/remove/{id}", name="paprec_catalog_region_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, Region $region)
    {
        $em = $this->getDoctrine()->getManager();

        $region->setDeleted(new \DateTime());
        if ($region->getPostalCodes() && count($region->getPostalCodes())) {
            foreach ($region->getPostalCodes() as $postalCode) {
                $postalCode->setRegion();
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_region_index');
    }

    /**
     * @Route("/region/removeMany/{ids}", name="paprec_catalog_region_removeMany")
     * @Security("has_role('ROLE_ADMIN')")
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
            $regions = $em->getRepository('PaprecCatalogBundle:Region')->findById($ids);
            foreach ($regions as $region) {
                $region->setDeleted(new \DateTime);
                if ($region->getPostalCodes() && count($region->getPostalCodes())) {
                    foreach ($region->getPostalCodes() as $postalCode) {
                        $postalCode->setRegion();
                    }
                }
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_region_index');
    }


}
