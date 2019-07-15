<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\PostalCode;
use Paprec\CatalogBundle\Form\PostalCodeType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostalCodeController extends Controller
{

    /**
     * @Route("/postalCode", name="paprec_catalog_postalCode_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:PostalCode:index.html.twig');
    }

    /**
     * @Route("/postalCode/loadList", name="paprec_catalog_postalCode_loadList")
     * @Security("has_role('ROLE_ADMIN')")
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

        $cols['id'] = array('label' => 'id', 'id' => 'pC.id', 'method' => array('getId'));
        $cols['code'] = array('label' => 'code', 'id' => 'pC.code', 'method' => array('getCode'));
        $cols['region'] = array('label' => 'region', 'id' => 'pC.region', 'method' => array('getRegion'));

        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(PostalCode::class)->createQueryBuilder('pC');


        $queryBuilder->select(array('pC'))
            ->where('pC.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('pC.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('pC.code', '?1')
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
     * @Route("/postalCode/export", name="paprec_catalog_postalCode_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(PostalCode::class)->createQueryBuilder('pC');

        $queryBuilder->select(array('pC'))
            ->where('pC.deleted IS NULL');

        $postalCodes = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Codes Postaux")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Code')
            ->setCellValue('C1', 'Coef de transport')
            ->setCellValue('D1', 'Coef de traitement')
            ->setCellValue('E1', 'Coef de traçabilité')
            ->setCellValue('F1', 'Commercial en charge')
            ->setCellValue('G1', 'Région');

        $phpExcelObject->getActiveSheet()->setTitle('Codes Postaux');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($postalCodes as $postalCode) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $postalCode->getId())
                ->setCellValue('B' . $i, $postalCode->getCode())
                ->setCellValue('C' . $i, $numberManager->denormalize($postalCode->getTransportRate()))
                ->setCellValue('D' . $i, $numberManager->denormalize($postalCode->getTreatmentRate()))
                ->setCellValue('E' . $i, $numberManager->denormalize($postalCode->getTraceabilityRate()))
                ->setCellValue('F' . $i, ($postalCode->getUserInCharge()) ? $postalCode->getUserInCharge()->getUsername() : '')
                ->setCellValue('G' . $i, ($postalCode->getRegion()) ? $postalCode->getRegion()->getName() : '');
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
     * @Route("/postalCode/view/{id}", name="paprec_catalog_postalCode_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, PostalCode $postalCode)
    {
        $postalCodeManager = $this->get('paprec_catalog.postal_code_manager');
        $postalCodeManager->isDeleted($postalCode, true);

        return $this->render('PaprecCatalogBundle:PostalCode:view.html.twig', array(
            'postalCode' => $postalCode
        ));
    }

    /**
     * @Route("/postalCode/add", name="paprec_catalog_postalCode_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();

        $numberManager = $this->get('paprec_catalog.number_manager');

        $postalCode = new PostalCode();

        $divisions = array();

        $form = $this->createForm(PostalCodeType::class, $postalCode, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $postalCode = $form->getData();
            $postalCode->setTransportRate($numberManager->normalize($postalCode->getTransportRate()));
            $postalCode->setTreatmentRate($numberManager->normalize($postalCode->getTreatmentRate()));
            $postalCode->setTraceabilityRate($numberManager->normalize($postalCode->getTraceabilityRate()));

            $postalCode->setDateCreation(new \DateTime);
            $postalCode->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($postalCode);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_postalCode_view', array(
                'id' => $postalCode->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:PostalCode:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/postalCode/edit/{id}", name="paprec_catalog_postalCode_edit")
     * @Security("has_role('ROLE_ADMIN')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editAction(Request $request, PostalCode $postalCode)
    {
        $user = $this->getUser();

        $numberManager = $this->get('paprec_catalog.number_manager');
        $postalCodeManager = $this->get('paprec_catalog.postal_code_manager');
        $postalCodeManager->isDeleted($postalCode, true);

        $postalCode->setTransportRate($numberManager->denormalize($postalCode->getTransportRate()));
        $postalCode->setTreatmentRate($numberManager->denormalize($postalCode->getTreatmentRate()));
        $postalCode->setTraceabilityRate($numberManager->denormalize($postalCode->getTraceabilityRate()));

        $form = $this->createForm(PostalCodeType::class, $postalCode);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $postalCode = $form->getData();
            $postalCode->setTransportRate($numberManager->normalize($postalCode->getTransportRate()));
            $postalCode->setTreatmentRate($numberManager->normalize($postalCode->getTreatmentRate()));
            $postalCode->setTraceabilityRate($numberManager->normalize($postalCode->getTraceabilityRate()));

            $postalCode->setDateUpdate(new \DateTime);
            $postalCode->setUserUpdate($user);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_postalCode_view', array(
                'id' => $postalCode->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:PostalCode:edit.html.twig', array(
            'form' => $form->createView(),
            'postalCode' => $postalCode
        ));
    }

    /**
     * @Route("/postalCode/remove/{id}", name="paprec_catalog_postalCode_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, PostalCode $postalCode)
    {
        $em = $this->getDoctrine()->getManager();

        $postalCode->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_postalCode_index');
    }

    /**
     * @Route("/postalCode/removeMany/{ids}", name="paprec_catalog_postalCode_removeMany")
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
            $postalCodes = $em->getRepository('PaprecCatalogBundle:PostalCode')->findById($ids);
            foreach ($postalCodes as $postalCode) {
                $postalCode->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_postalCode_index');
    }


}
