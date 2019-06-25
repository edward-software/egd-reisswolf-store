<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\ContactUs;
use Paprec\CommercialBundle\Form\ContactUs\ContactUsEditType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

class ContactUsController extends Controller
{
    /**
     * @Route("/contactUs", name="paprec_commercial_contactUs_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ContactUs:index.html.twig');
    }

    /**
     * @Route("/contactUs/loadList", name="paprec_commercial_contactUs_loadList")
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

        // Récupération du type de catégorie souhaité (DI, CHANTIER, D3E ou '')
        $typeContactUs = $request->get('typeContactUs');

        $cols['id'] = array('label' => 'id', 'id' => 'o.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'o.businessName', 'method' => array('getBusinessName'));
        $cols['email'] = array('label' => 'email', 'id' => 'o.email', 'method' => array('getEmail'));
        $cols['treatmentStatus'] = array('label' => 'treatmentStatus', 'id' => 'o.treatmentStatus', 'method' => array('getTreatmentStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'o.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        if ($typeContactUs === '') {
            $queryBuilder->select(array('o'))
                ->from('PaprecCommercialBundle:ContactUs', 'o')
                ->where('o.deleted IS NULL')
                ->andWhere('o.division is NULL'); // Récupération des ContactUss qui n'ont pas de division
        } else  {
            $queryBuilder->select(array('o'))
                ->from('PaprecCommercialBundle:ContactUs', 'o')
                ->where('o.deleted IS NULL')
                ->andWhere('o.division LIKE \'%' . $typeContactUs . '%\''); // Récupération des ContactUss du type voulu
        }

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
     * @Route("/contactUs/export", name="paprec_commercial_contactUs_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:ContactUs', 'o')
            ->where('o.deleted IS NULL');

        $contactUss = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Demandes de contact")
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
            ->setCellValue('I1', 'Mon besoin')
            ->setCellValue('J1', 'Division')
            ->setCellValue('K1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Demandes de contact');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($contactUss as $contactUs) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $contactUs->getId())
                ->setCellValue('B' . $i, $contactUs->getBusinessName())
                ->setCellValue('C' . $i, $contactUs->getCivility())
                ->setCellValue('D' . $i, $contactUs->getLastName())
                ->setCellValue('E' . $i, $contactUs->getFirstName())
                ->setCellValue('F' . $i, $contactUs->getEmail())
                ->setCellValue('G' . $i, $contactUs->getPhone())
                ->setCellValue('H' . $i, $contactUs->getTreatmentStatus())
                ->setCellValue('I' . $i, $contactUs->getNeed())
                ->setCellValue('J' . $i, $contactUs->getDivision())
                ->setCellValue('K' . $i, $contactUs->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Contacts-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/contactUs/view/{id}", name="paprec_commercial_contactUs_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, ContactUs $contactUs)
    {
        $contactUsManager = $this->get('paprec_commercial.contact_us_manager');
        $contactUsManager->isDeleted($contactUs, true);

        return $this->render('PaprecCommercialBundle:ContactUs:view.html.twig', array(
            'contactUs' => $contactUs
        ));
    }

    /**
     * @Route("/contactUs/edit/{id}", name="paprec_commercial_contactUs_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function editAction(Request $request, ContactUs $contactUs)
    {
        $contactUsManager = $this->get('paprec_commercial.contact_us_manager');
        $contactUsManager->isDeleted($contactUs, true);

        $status = array();
        foreach ($this->getParameter('paprec_treatment_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ContactUsEditType::class, $contactUs, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contactUs = $form->getData();
            $contactUs->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_contactUs_view', array(
                'id' => $contactUs->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ContactUs:edit.html.twig', array(
            'form' => $form->createView(),
            'contactUs' => $contactUs
        ));
    }

    /**
     * @Route("/contactUs/remove/{id}", name="paprec_commercial_contactUs_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function removeAction(Request $request, ContactUs $contactUs)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($contactUs->getAttachedFiles() as $file) {
            $this->removeFile($this->getParameter('paprec_commercial.contact_us.files_path') . '/' . $file);
            $contactUs->setAttachedFiles();
        }

        $contactUs->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_contactUs_index');
    }

    /**
     * @Route("/contactUs/removeMany/{ids}", name="paprec_commercial_contactUs_removeMany")
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
            $contactUss = $em->getRepository('PaprecCommercialBundle:ContactUs')->findById($ids);
            foreach ($contactUss as $contactUs) {
                if ($contactUs->getAttachedFiles()) {
                    foreach ($contactUs->getAttachedFiles() as $file) {
                        $this->removeFile($this->getParameter('paprec_commercial.contact_us.files_path') . '/' . $file);
                        $contactUs->setAttachedFiles();
                    }
                }
                $contactUs->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_contactUs_index');
    }


    /**
     * Supprimme un fichier du sytème de fichiers
     *
     * @param $path
     */
    public function removeFile($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOException $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @Route("/contactUs/{id}/downloadAttachedFiles", name="paprec_commercial_contactUs_downloadAttachedFiles")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function downloadAttachedFilesAction(ContactUs $contactUs)
    {
        $path = $this->getParameter('paprec_commercial.contact_us.files_path');
        $zipname = 'demandeContacts' . $contactUs->getId() . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $cpt = 1;
        foreach ($contactUs->getAttachedFiles() as $file) {
            $extension = pathinfo($path . '/' . $file, PATHINFO_EXTENSION);
            $newFilename = "Demande-contact-" . $contactUs->getId() . '-PJ' . $cpt . '.' . $extension;

            $filename= $path . '/' . $file;
            $zip->addFile($filename, $newFilename);
            $cpt++;
        }
        $zip->close();

        $name = $zipname;
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);
    }

}
