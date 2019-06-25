<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Form\QuoteRequest\QuoteRequestAssociatedQuoteType;
use Paprec\CommercialBundle\Form\QuoteRequest\QuoteRequestEditType;
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

class QuoteRequestController extends Controller
{
    /**
     * @Route("/quoteRequest", name="paprec_commercial_quoteRequest_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:QuoteRequest:index.html.twig');
    }

    /**
     * @Route("/quoteRequest/loadList", name="paprec_commercial_quoteRequest_loadList")
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
        // Récupération du type de catégorie souhaité (DI, CHANTIER, D3E)
        $typeQuoteRequest = $request->get('typeQuoteRequest');

        $cols['id'] = array('label' => 'id', 'id' => 'o.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'o.businessName', 'method' => array('getBusinessName'));
        $cols['email'] = array('label' => 'email', 'id' => 'o.email', 'method' => array('getEmail'));
        $cols['quoteStatus'] = array('label' => 'quoteStatus', 'id' => 'o.quoteStatus', 'method' => array('getQuoteStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'o.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:QuoteRequest', 'o')
            ->where('o.deleted IS NULL')
            ->andWhere('o.division LIKE \'%' . $typeQuoteRequest . '%\''); // Récupération des QuoteRequests du type voulu


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
     * @Route("/quoteRequest/export", name="paprec_commercial_quoteRequest_export")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function exportAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:QuoteRequest', 'o')
            ->where('o.deleted IS NULL');

        $quoteRequests = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Demandes de devis")
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
            ->setCellValue('M1', 'Agence associée')
            ->setCellValue('N1', 'Résumé du besoin')
            ->setCellValue('O1', 'Fréquence')
            ->setCellValue('P1', 'Tonnage')
            ->setCellValue('Q1', 'N° Kookabura')
            ->setCellValue('R1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Demandes de devis');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($quoteRequests as $quoteRequest) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $quoteRequest->getId())
                ->setCellValue('B' . $i, $quoteRequest->getBusinessName())
                ->setCellValue('C' . $i, $quoteRequest->getCivility())
                ->setCellValue('D' . $i, $quoteRequest->getLastName())
                ->setCellValue('E' . $i, $quoteRequest->getFirstName())
                ->setCellValue('F' . $i, $quoteRequest->getEmail())
                ->setCellValue('G' . $i, $quoteRequest->getPhone())
                ->setCellValue('H' . $i, $quoteRequest->getQuoteStatus())
                ->setCellValue('I' . $i, $quoteRequest->getNeed())
                ->setCellValue('J' . $i, $numberManager->denormalize($quoteRequest->getGeneratedTurnover()))
                ->setCellValue('K' . $i, $quoteRequest->getDivision())
                ->setCellValue('L' . $i, $quoteRequest->getPostalCode())
                ->setCellValue('M' . $i, $quoteRequest->getAgency())
                ->setCellValue('N' . $i, $quoteRequest->getSummary())
                ->setCellValue('O' . $i, $quoteRequest->getFrequency())
                ->setCellValue('P' . $i, $quoteRequest->getTonnage())
                ->setCellValue('Q' . $i, $quoteRequest->getKookaburaNumber())
                ->setCellValue('R' . $i, $quoteRequest->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Demandes-Devis-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/quoteRequest/view/{id}", name="paprec_commercial_quoteRequest_view")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function viewAction(Request $request, QuoteRequest $quoteRequest)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);

        $form = $this->createForm(QuoteRequestAssociatedQuoteType::class, $quoteRequest);
        return $this->render('PaprecCommercialBundle:QuoteRequest:view.html.twig', array(
            'quoteRequest' => $quoteRequest,
            'formAddAssociatedQuote' => $form->createView()
        ));
    }

    /**
     * @Route("/quoteRequest/edit/{id}", name="paprec_commercial_quoteRequest_edit")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function editAction(Request $request, QuoteRequest $quoteRequest)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);

        $status = array();
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $quoteRequest->setGeneratedTurnover($numberManager->denormalize($quoteRequest->getGeneratedTurnover()));

        $form = $this->createForm(QuoteRequestEditType::class, $quoteRequest, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quoteRequest = $form->getData();
            $quoteRequest->setGeneratedTurnover($numberManager->normalize($quoteRequest->getGeneratedTurnover()));

            $quoteRequest->setDateUpdate(new \DateTime());

            if ($quoteRequest->getAssociatedQuote() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $associatedQuote = $quoteRequest->getAssociatedQuote();
                $associatedQuoteFileName = md5(uniqid()) . '.' . $associatedQuote->guessExtension();

                $associatedQuote->move($this->getParameter('paprec_commercial.quote_request.files_path'), $associatedQuoteFileName);

                $quoteRequest->setAssociatedQuote($associatedQuoteFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:QuoteRequest:edit.html.twig', array(
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * @Route("/quoteRequest/remove/{id}", name="paprec_commercial_quoteRequest_remove")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function removeAction(Request $request, QuoteRequest $quoteRequest)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($quoteRequest->getAttachedFiles() as $file) {
            $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
            $quoteRequest->setAttachedFiles();
        }
        if (!empty($quoteRequest->getAssociatedQuote())) {
            $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $quoteRequest->getAssociatedQuote());
            $quoteRequest->setAssociatedQuote();
        }

        $quoteRequest->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
    }

    /**
     * @Route("/quoteRequest/removeMany/{ids}", name="paprec_commercial_quoteRequest_removeMany")
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
            $quoteRequests = $em->getRepository('PaprecCommercialBundle:QuoteRequest')->findById($ids);
            foreach ($quoteRequests as $quoteRequest) {
                foreach ($quoteRequest->getAttachedFiles() as $file) {
                    $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
                    $quoteRequest->setAttachedFiles();
                }
                if (!empty($quoteRequest->getAssociatedQuote())) {
                    $this->removeFile($this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $file);
                    $quoteRequest->setAssociatedQuote();
                }
                $quoteRequest->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
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
     * @Route("/quoteRequest/addAssociatedQuote/{id}", name="paprec_commercial_quoteRequest_addAssociatedQuote")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws Exception
     */
    public function addAssociatedQuoteAction(Request $request, QuoteRequest $quoteRequest)
    {

        $form = $this->createForm(QuoteRequestAssociatedQuoteType::class, $quoteRequest);

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $quoteRequest = $form->getData();
            $quoteRequest->setDateUpdate(new \DateTime());

            if ($quoteRequest->getAssociatedQuote() instanceof UploadedFile) {

                $associatedQuote = $quoteRequest->getAssociatedQuote();
                $associatedQuoteFileName = md5(uniqid()) . '.' . $associatedQuote->guessExtension();

                $associatedQuote->move($this->getParameter('paprec_commercial.quote_request.files_path'), $associatedQuoteFileName);

                $quoteRequest->setAssociatedQuote($associatedQuoteFileName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
                'id' => $quoteRequest->getId()
            ));
        }
        return $this->render('PaprecCommercialBundle:QuoteRequest:view.html.twig', array(
            'quoteRequest' => $quoteRequest,
            'formAddAssociatedQuote' => $form->createView()
        ));
    }


    /**
     * @Route("/quoteRequest/{id}/downloadAssociatedQuote", name="paprec_commercial_quoteRequest_downloadAssociatedQuote")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function downloadAssociatedQuoteAction(QuoteRequest $quoteRequest)
    {
        $filename = $quoteRequest->getAssociatedQuote();
        $path = $this->getParameter('paprec_commercial.quote_request.files_path');
        $file = $path . '/' . $filename;
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $newFilename = "Demande-Devis-" . $quoteRequest->getId() . '-Devis-Associe.' . $extension;

        if (file_exists($file)) {
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
     * @Route("/quoteRequest/{id}/downloadAttachedFiles", name="paprec_commercial_quoteRequest_downloadAttachedFiles")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     */
    public function downloadAttachedFilesAction(QuoteRequest $quoteRequest)
    {
        $path = $this->getParameter('paprec_commercial.quote_request.files_path');
        $zipname = 'demandeDevis-' . $quoteRequest->getId() . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $cpt = 1;
        foreach ($quoteRequest->getAttachedFiles() as $file) {
            $extension = pathinfo($path . '/' . $file, PATHINFO_EXTENSION);
            $newFilename = "Demande-devis-" . $quoteRequest->getId() . '-PJ' . $cpt . '.' . $extension;

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
     * @Route("/quoteRequest/{id}/sendAsssociatedQuote", name="paprec_commercial_quoteRequest_sendAssociatedQuote")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_COMMERCIAL_DIVISION')")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sendAssociatedQuoteAction(QuoteRequest $quoteRequest)
    {
        $quoteRequestManager = $this->get('paprec_commercial.quote_request_manager');
        $quoteRequestManager->isDeleted($quoteRequest, true);

        if ($quoteRequest->getAssociatedQuote() == null) {
            $this->get('session')->getFlashBag()->add('error', 'noUploadedQuoteFound');
        } else {
            $sendQuote = $quoteRequestManager->sendAssociatedQuoteMail($quoteRequest);
            if($sendQuote) {
                $this->get('session')->getFlashBag()->add('success', 'associatedQuoteSent');
            } else {
                $this->get('session')->getFlashBag()->add('error', 'associatedQuoteNotSent');
            }
        }
        return $this->redirectToRoute('paprec_commercial_quoteRequest_view', array(
            'id' => $quoteRequest->getId()
        ));
    }
}
