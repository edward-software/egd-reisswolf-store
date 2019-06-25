<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate;
use Paprec\CommercialBundle\Form\QuoteRequestNonCorporate\QuoteRequestNonCorporateAssociatedQuoteType;
use Paprec\CommercialBundle\Form\QuoteRequestNonCorporate\QuoteRequestNonCorporateEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

class QuoteRequestNonCorporateController extends Controller
{
    /**
     * @Route("/quoteRequestNonCorporate", name="paprec_commercial_quoteRequestNonCorporate_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate:index.html.twig');
    }

    /**
     * @Route("/quoteRequestNonCorporate/loadList", name="paprec_commercial_quoteRequestNonCorporate_loadList")
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
        $cols['quoteStatus'] = array('label' => 'quoteStatus', 'id' => 'o.quoteStatus', 'method' => array('getQuoteStatus'));
        $cols['customerType'] = array('label' => 'customerType', 'id' => 'o.customerType', 'method' => array('getCustomerType'));
        $cols['division'] = array('label' => 'division', 'id' => 'o.division', 'method' => array('getDivision'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'o.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:QuoteRequestNonCorporate', 'o')
            ->where('o.deleted IS NULL'); // Récupération des QuoteRequestNonCorporates du type voulu


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('o.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('o.businessName', '?1'),
                    $queryBuilder->expr()->like('o.email', '?1'),
                    $queryBuilder->expr()->like('o.quoteStatus', '?1'),
                    $queryBuilder->expr()->like('o.customerType', '?1'),
                    $queryBuilder->expr()->like('o.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
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
     * @Route("/quoteRequestNonCorporate/export", name="paprec_commercial_quoteRequestNonCorporate_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:QuoteRequestNonCorporate', 'o')
            ->where('o.deleted IS NULL');

        $quoteRequestNonCorporates = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Demandes de devis non entreprise")
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
            ->setCellValue('J1', 'CA généré')
            ->setCellValue('K1', 'Division')
            ->setCellValue('L1', 'Code postal')
            ->setCellValue('M1', 'Type de client')
            ->setCellValue('N1', 'Agence associée')
            ->setCellValue('O1', 'Résumé du besoin')
            ->setCellValue('P1', 'Fréquence')
            ->setCellValue('Q1', 'Tonnage')
            ->setCellValue('R1', 'N° Kookabura')
            ->setCellValue('S1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Demandes de devis non entreprise');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($quoteRequestNonCorporates as $quoteRequestNonCorporate) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $quoteRequestNonCorporate->getId())
                ->setCellValue('B' . $i, $quoteRequestNonCorporate->getBusinessName())
                ->setCellValue('C' . $i, $quoteRequestNonCorporate->getCivility())
                ->setCellValue('D' . $i, $quoteRequestNonCorporate->getLastName())
                ->setCellValue('E' . $i, $quoteRequestNonCorporate->getFirstName())
                ->setCellValue('F' . $i, $quoteRequestNonCorporate->getEmail())
                ->setCellValue('G' . $i, $quoteRequestNonCorporate->getPhone())
                ->setCellValue('H' . $i, $quoteRequestNonCorporate->getQuoteStatus())
                ->setCellValue('I' . $i, $quoteRequestNonCorporate->getNeed())
                ->setCellValue('J' . $i, $numberManager->denormalize($quoteRequestNonCorporate->getGeneratedTurnover()))
                ->setCellValue('K' . $i, $quoteRequestNonCorporate->getDivision())
                ->setCellValue('L' . $i, $quoteRequestNonCorporate->getPostalCode())
                ->setCellValue('M' . $i, $quoteRequestNonCorporate->getCustomerType())
                ->setCellValue('N' . $i, $quoteRequestNonCorporate->getAgency())
                ->setCellValue('O' . $i, $quoteRequestNonCorporate->getSummary())
                ->setCellValue('P' . $i, $quoteRequestNonCorporate->getFrequency())
                ->setCellValue('Q' . $i, $quoteRequestNonCorporate->getTonnage())
                ->setCellValue('R' . $i, $quoteRequestNonCorporate->getKookaburaNumber())
                ->setCellValue('S' . $i, $quoteRequestNonCorporate->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Demandes-Devis-Non-Entreprise' . date('Y-m-d') . '.xlsx';

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
     * @Route("/quoteRequestNonCorporate/view/{id}", name="paprec_commercial_quoteRequestNonCorporate_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function viewAction(Request $request, QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');
        $quoteRequestNonCorporateManager->isDeleted($quoteRequestNonCorporate, true);

        $form = $this->createForm(QuoteRequestNonCorporateAssociatedQuoteType::class, $quoteRequestNonCorporate);

        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate:view.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate,
            'formAddAssociatedQuote' => $form->createView()
        ));
    }

    /**
     * @Route("/quoteRequestNonCorporate/edit/{id}", name="paprec_commercial_quoteRequestNonCorporate_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function editAction(Request $request, QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');
        $quoteRequestNonCorporateManager->isDeleted($quoteRequestNonCorporate, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $quoteRequestNonCorporate->setGeneratedTurnover($numberManager->denormalize($quoteRequestNonCorporate->getGeneratedTurnover()));

        $form = $this->createForm(QuoteRequestNonCorporateEditType::class, $quoteRequestNonCorporate, array(
            'status' => $status,
            'division' => $divisions
        ));


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quoteRequestNonCorporate = $form->getData();
            $quoteRequestNonCorporate->setGeneratedTurnover($numberManager->normalize($quoteRequestNonCorporate->getGeneratedTurnover()));

            $quoteRequestNonCorporate->setDateUpdate(new \DateTime());

            if ($quoteRequestNonCorporate->getAssociatedQuote() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $associatedQuote = $quoteRequestNonCorporate->getAssociatedQuote();
                $associatedQuoteFileName = md5(uniqid()) . '.' . $associatedQuote->guessExtension();

                $associatedQuote->move($this->getParameter('paprec_commercial.quote_request.files_path'), $associatedQuoteFileName);

                $quoteRequestNonCorporate->setAssociatedQuote($associatedQuoteFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequestNonCorporate_view', array(
                'id' => $quoteRequestNonCorporate->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate:edit.html.twig', array(
            'form' => $form->createView(),
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
        ));
    }

    /**
     * @Route("/quoteRequestNonCorporate/remove/{id}", name="paprec_commercial_quoteRequestNonCorporate_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function removeAction(Request $request, QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($quoteRequestNonCorporate->getAttachedFiles() as $file) {
            $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
            $quoteRequestNonCorporate->setAttachedFiles();
        }
        if (!empty($quoteRequestNonCorporate->getAssociatedQuote())) {
            $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $quoteRequestNonCorporate->getAssociatedQuote());
            $quoteRequestNonCorporate->setAssociatedQuote();
        }

        $quoteRequestNonCorporate->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_quoteRequestNonCorporate_index');
    }

    /**
     * @Route("/quoteRequestNonCorporate/removeMany/{ids}", name="paprec_commercial_quoteRequestNonCorporate_removeMany")
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
            $quoteRequestNonCorporates = $em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->findById($ids);
            foreach ($quoteRequestNonCorporates as $quoteRequestNonCorporate) {
                if ($quoteRequestNonCorporate->getAttachedFiles()) {
                    foreach ($quoteRequestNonCorporate->getAttachedFiles() as $file) {
                        $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
                        $quoteRequestNonCorporate->setAttachedFiles();
                    }
                }
                if (!empty($quoteRequestNonCorporate->getAssociatedQuote())) {
                    $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
                    $quoteRequestNonCorporate->setAssociatedQuote();
                }
                $quoteRequestNonCorporate->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequestNonCorporate_index');
    }


    /**
     * Supprimme un fichier du sytème de fichiers
     *
     * @param $path
     */
    public
    function removeFile($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @Route("/quoteRequestNonCorporate/addAssociatedQuote/{id}", name="paprec_commercial_quoteRequestNonCorporate_addAssociatedQuote")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function addAssociatedQuoteAction(Request $request, QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {

        $form = $this->createForm(QuoteRequestNonCorporateAssociatedQuoteType::class, $quoteRequestNonCorporate);

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $quoteRequestNonCorporate = $form->getData();
            $quoteRequestNonCorporate->setDateUpdate(new \DateTime());

            if ($quoteRequestNonCorporate->getAssociatedQuote() instanceof UploadedFile) {

                $associatedQuote = $quoteRequestNonCorporate->getAssociatedQuote();
                $associatedQuoteFileName = md5(uniqid()) . '.' . $associatedQuote->guessExtension();

                $associatedQuote->move($this->getParameter('paprec_commercial.quote_request.files_path'), $associatedQuoteFileName);

                $quoteRequestNonCorporate->setAssociatedQuote($associatedQuoteFileName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequestNonCorporate->getId()
            ));
        }
        return $this->render('PaprecCommercialBundle:QuoteRequestNonCorporate:view.html.twig', array(
            'quoteRequestNonCorporate' => $quoteRequestNonCorporate,
            'formAddAssociatedQuote' => $form->createView()
        ));
    }


    /**
     * @Route("/quoteRequestNonCorporate/{id}/downloadAssociatedQuote", name="paprec_commercial_quoteRequestNonCorporate_downloadAssociatedQuote")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function downloadAssociatedQuoteAction(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $filename = $quoteRequestNonCorporate->getAssociatedQuote();
        $path = $this->getParameter('paprec_commercial.quote_request.files_path');
        $file = $path . '/' . $filename;
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $newFilename = "Demande-Devis-Non-Entreprise" . $quoteRequestNonCorporate->getId() . '-Devis-Associe.' . $extension;

        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($newFilename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    /**
     * @Route("/quoteRequestNonCorporate/{id}/downloadAttachedFiles", name="paprec_commercial_quoteRequestNonCorporate_downloadAttachedFiles")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function downloadAttachedFilesAction(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $path = $this->getParameter('paprec_commercial.quote_request.files_path');
        $zipname = 'demandeDevisNonEntreprise-'.$quoteRequestNonCorporate->getId() . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $cpt = 1;
        foreach ($quoteRequestNonCorporate->getAttachedFiles() as $file) {
            $extension = pathinfo($path . '/' . $file, PATHINFO_EXTENSION);
            $newFilename = "Demande-devis-non-entreprise" . $quoteRequestNonCorporate->getId() . '-PJ' . $cpt . '.' . $extension;

            $filename = $path . '/' . $file;
            $zip->addFile($filename, $newFilename);
            $cpt++;
        }
        $zip->close();

        $name = $zipname;
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);
    }

    /**
     * @Route("/quoteRequestNonCorporate/{id}/sendAsssociatedQuote", name="paprec_commercial_quoteRequestNonCorporate_sendAssociatedQuote")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sendAssociatedQuoteAction(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');
        $quoteRequestNonCorporateManager->isDeleted($quoteRequestNonCorporate, true);

        if ($quoteRequestNonCorporate->getAssociatedQuote() == null) {
            $this->get('session')->getFlashBag()->add('error', 'noUploadedQuoteFound');
        } else {
            $sendQuote = $quoteRequestNonCorporateManager->sendAssociatedQuoteMail($quoteRequestNonCorporate);
            if($sendQuote) {
                $this->get('session')->getFlashBag()->add('success', 'associatedQuoteSent');
            } else {
                $this->get('session')->getFlashBag()->add('error', 'associatedQuoteNotSent');
            }
        }
        return $this->redirectToRoute('paprec_commercial_quoteRequestNonCorporate_view', array(
            'id' => $quoteRequestNonCorporate->getId()
        ));
    }

}
