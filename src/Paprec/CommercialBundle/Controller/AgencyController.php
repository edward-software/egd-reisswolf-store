<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Form\Agency\AgenceType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgencyController extends Controller
{
    /**
     * @Route("/agency", name="paprec_commercial_agence_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:Agency:index.html.twig');
    }

    /**
     * @Route("/agency/loadList", name="paprec_commercial_agence_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'a.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'a.name', 'method' => array('getName'));
        $cols['address'] = array('label' => 'address', 'id' => 'a.address', 'method' => array('getAddress'));
        $cols['postalCode'] = array('label' => 'postalCode', 'id' => 'a.postalCode', 'method' => array('getPostalCode'));
        $cols['city'] = array('label' => 'city', 'id' => 'a.city', 'method' => array('getCity'));
        $cols['isDisplayed'] = array('label' => 'isDisplayed', 'id' => 'a.isDisplayed', 'method' => array('getIsDisplayed'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCommercialBundle:Agency', 'a')
            ->where('a.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('a.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('a.name', '?1'),
                    $queryBuilder->expr()->like('a.address', '?1'),
                    $queryBuilder->expr()->like('a.postalCode', '?1'),
                    $queryBuilder->expr()->like('a.city', '?1'),
                    $queryBuilder->expr()->like('a.isDisplayed', '?1')
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
     * @Route("/agency/export", name="paprec_commercial_agence_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('a'))
            ->from('PaprecCommercialBundle:Agency', 'a')
            ->where('a.deleted IS NULL');

        $agences = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Agencies")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Adresse')
            ->setCellValue('D1', 'Code Postal')
            ->setCellValue('E1', 'Ville')
            ->setCellValue('F1', 'Téléphone')
            ->setCellValue('G1', 'Latitude')
            ->setCellValue('H1', 'Longitude')
            ->setCellValue('I1', 'Statut d\'affichage')
            ->setCellValue('J1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Agencies');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($agences as $agence) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $agence->getId())
                ->setCellValue('B' . $i, $agence->getName())
                ->setCellValue('C' . $i, $agence->getAddress())
                ->setCellValue('D' . $i, $agence->getPostalCode())
                ->setCellValue('E' . $i, $agence->getCity())
                ->setCellValue('F' . $i, $agence->getPhone())
                ->setCellValue('G' . $i, $agence->getLatitude())
                ->setCellValue('H' . $i, $agence->getLongitude())
                ->setCellValue('I' . $i, $agence->getIsDisplayed())
                ->setCellValue('J' . $i, $agence->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Agencies-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/agency/view/{id}", name="paprec_commercial_agence_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function viewAction(Request $request, Agency $agence)
    {
        $agencyManager = $this->get('paprec_commercial.agency_manager');
        $agencyManager->isDeleted($agence, true);

        return $this->render('PaprecCommercialBundle:Agency:view.html.twig', array(
            'agency' => $agence
        ));
    }

    /**
     * @Route("/agency/add", name="paprec_commercial_agence_add")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {

        $agence = new Agency();

        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(AgenceType::class, $agence, array(
            'divisions' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agence = $form->getData();
            $agence->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($agence);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_agence_view', array(
                'id' => $agence->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:Agency:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/agency/edit/{id}", name="paprec_commercial_agence_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, Agency $agence)
    {
        $agencyManager = $this->get('paprec_commercial.agency_manager');
        $agencyManager->isDeleted($agence, true);


        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(AgenceType::class, $agence, array(
            'divisions' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agence = $form->getData();
            $agence->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_agence_view', array(
                'id' => $agence->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:Agency:edit.html.twig', array(
            'form' => $form->createView(),
            'agency' => $agence
        ));
    }

    /**
     * @Route("/agency/remove/{id}", name="paprec_commercial_agence_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function removeAction(Request $request, Agency $agence)
    {
        $em = $this->getDoctrine()->getManager();

        $agence->setDeleted(new \DateTime);
        $agence->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_agence_index');
    }

    /**
     * @Route("/agency/removeMany/{ids}", name="paprec_commercial_agence_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
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
            $agences = $em->getRepository('PaprecCommercialBundle:Agency')->findById($ids);
            foreach ($agences as $agence) {
                $agence->setDeleted(new \DateTime);
                $agence->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_agence_index');
    }

}
