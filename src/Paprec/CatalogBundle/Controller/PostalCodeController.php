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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:PostalCode:index.html.twig');
    }

    /**
     * @Route("/postalCode/loadList", name="paprec_catalog_postalCode_loadList")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
        $cols['codes'] = array('label' => 'codes', 'id' => 'p.codes', 'method' => array('getCodes'));
        $cols['division'] = array('label' => 'division', 'id' => 'c.division', 'method' => array('getDivision'));
        $cols['rate'] = array('label' => 'rate', 'id' => 'p.rate', 'method' => array('getRate'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:PostalCode', 'p')
            ->where('p.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.codes', '?1'),
                    $queryBuilder->expr()->like('p.division', '?1'),
                    $queryBuilder->expr()->like('p.rate', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['rate'] = $numberManager->formatAmount($data['rate'], null, $request->getLocale());
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
     * @Route("/postalCode/export", name="paprec_catalog_postalCode_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function exportAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:PostalCode', 'p')
            ->where('p.deleted IS NULL');

        $postalCodes = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Codes Postaux")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Codes')
            ->setCellValue('C1', 'Division')
            ->setCellValue('D1', 'Coef. Mult.');

        $phpExcelObject->getActiveSheet()->setTitle('Codes Postaux');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($postalCodes as $postalCode) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $postalCode->getId())
                ->setCellValue('B' . $i, $postalCode->getCodes())
                ->setCellValue('C' . $i, $postalCode->getDivision())
                ->setCellValue('D' . $i, $numberManager->denormalize($postalCode->getRate()));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Codes-Postaux-' . date('Y-m-d') . '.xlsx';

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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $postalCode = new PostalCode();

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(PostalCodeType::class, $postalCode, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $postalCode = $form->getData();
            $postalCode->setRate($numberManager->normalize($postalCode->getRate()));

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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editAction(Request $request, PostalCode $postalCode)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $postalCodeManager = $this->get('paprec_catalog.postal_code_manager');
        $postalCodeManager->isDeleted($postalCode, true);

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $postalCode->setRate($numberManager->denormalize($postalCode->getRate()));

        $form = $this->createForm(PostalCodeType::class, $postalCode, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $postalCode = $form->getData();
            $postalCode->setRate($numberManager->normalize($postalCode->getRate()));

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
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MANAGER_DIVISION')")
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
            $postalCodes = $em->getRepository('PaprecCatalogBundle:PostalCode')->findById($ids);
            foreach ($postalCodes as $postalCode) {
                $postalCode->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_postalCode_index');
    }


}
