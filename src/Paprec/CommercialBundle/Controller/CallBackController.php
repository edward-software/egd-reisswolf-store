<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\CallBack;
use Paprec\CommercialBundle\Form\CallBack\CallBackEditType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CallBackController extends Controller
{
    /**
     * @Route("/callBack", name="paprec_commercial_callBack_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:CallBack:index.html.twig');
    }

    /**
     * @Route("/callBack/loadList", name="paprec_commercial_callBack_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'o.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'o.businessName', 'method' => array('getBusinessName'));
        $cols['email'] = array('label' => 'email', 'id' => 'o.email', 'method' => array('getEmail'));
        $cols['treatmentStatus'] = array('label' => 'treatmentStatus', 'id' => 'o.treatmentStatus', 'method' => array('getTreatmentStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'o.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:CallBack', 'o')
            ->where('o.deleted IS NULL');


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('o.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('o.businessName', '?1'),
                    $queryBuilder->expr()->like('o.email', '?1'),
                    $queryBuilder->expr()->like('o.treatmentStatus', '?1'),
                    $queryBuilder->expr()->like('o.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['treatmentStatus'] = $this->container->get('translator')->trans("Commercial.TreatmentStatusList." . $data['treatmentStatus']);
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
     * @Route("/callBack/export", name="paprec_commercial_callBack_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:CallBack', 'o')
            ->where('o.deleted IS NULL');

        $callBacks = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Demandes de rappel")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Civilité')
            ->setCellValue('D1', 'Nom')
            ->setCellValue('E1', 'Prénom')
            ->setCellValue('F1', 'Email')
            ->setCellValue('G1', 'Téléphone')
            ->setCellValue('H1', 'Statut')
            ->setCellValue('I1', 'Date/Heure rappel')
            ->setCellValue('J1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Demandes de rappel');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($callBacks as $callBack) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $callBack->getId())
                ->setCellValue('B' . $i, $callBack->getBusinessName())
                ->setCellValue('C' . $i, $callBack->getCivility())
                ->setCellValue('D' . $i, $callBack->getLastName())
                ->setCellValue('E' . $i, $callBack->getFirstName())
                ->setCellValue('F' . $i, $callBack->getEmail())
                ->setCellValue('G' . $i, $callBack->getPhone())
                ->setCellValue('H' . $i, $callBack->getTreatmentStatus())
                ->setCellValue('I' . $i, $callBack->getDateCallBack())
                ->setCellValue('J' . $i, $callBack->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Rappels-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/callBack/view/{id}", name="paprec_commercial_callBack_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, CallBack $callBack)
    {
        $callBackManager = $this->get('paprec_commercial.call_back_manager');
        $callBackManager->isDeleted($callBack, true);

        return $this->render('PaprecCommercialBundle:CallBack:view.html.twig', array(
            'callBack' => $callBack
        ));
    }

    /**
     * @Route("/callBack/edit/{id}", name="paprec_commercial_callBack_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function editAction(Request $request, CallBack $callBack)
    {
        $callBackManager = $this->get('paprec_commercial.call_back_manager');
        $callBackManager->isDeleted($callBack, true);

        $status = array();
        foreach ($this->getParameter('paprec_treatment_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(CallBackEditType::class, $callBack, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $callBack = $form->getData();
            $callBack->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_callBack_view', array(
                'id' => $callBack->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:CallBack:edit.html.twig', array(
            'form' => $form->createView(),
            'callBack' => $callBack
        ));
    }

    /**
     * @Route("/callBack/remove/{id}", name="paprec_commercial_callBack_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function removeAction(Request $request, CallBack $callBack)
    {
        $em = $this->getDoctrine()->getManager();

        $callBack->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_callBack_index');
    }

    /**
     * @Route("/callBack/removeMany/{ids}", name="paprec_commercial_callBack_removeMany")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
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
            $callBacks = $em->getRepository('PaprecCommercialBundle:CallBack')->findById($ids);
            foreach ($callBacks as $callBack) {

                $callBack->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_callBack_index');
    }
}
