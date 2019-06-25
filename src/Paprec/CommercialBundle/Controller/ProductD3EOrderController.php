<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EOrderLine;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderInvoiceType;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderLineAddType;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderLineEditType;
use Paprec\CommercialBundle\Form\ProductD3EOrder\ProductD3EOrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

class ProductD3EOrderController extends Controller
{

    /**
     * @Route("/productD3EOrder", name="paprec_commercial_productD3EOrder_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductD3EOrder:index.html.twig');
    }

    /**
     * @Route("/productD3EOrder/loadList", name="paprec_commercial_productD3EOrder_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
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
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'p.businessName', 'method' => array('getBusinessName'));
        $cols['totalAmount'] = array('label' => 'totalAmount', 'id' => 'p.totalAmount', 'method' => array('getTotalAmount'));
        $cols['orderStatus'] = array('label' => 'orderStatus', 'id' => 'p.orderStatus', 'method' => array('getOrderStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'p.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductD3EOrder', 'p')
            ->where('p.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.businessName', '?1'),
                    $queryBuilder->expr()->like('p.totalAmount', '?1'),
                    $queryBuilder->expr()->like('p.orderStatus', '?1'),
                    $queryBuilder->expr()->like('p.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        // Reformatage de certaines données
        $tmp = array();
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['totalAmount'] = $numberManager->formatAmount($data['totalAmount'], 'EUR', $request->getLocale());
            $line['orderStatus'] = $this->container->get('translator')->trans("Commercial.OrderStatusList." . $data['orderStatus']);
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
     * @Route("/productD3EOrder/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_productD3EOrder_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function exportAction(Request $request, $dateStart, $dateEnd, $status)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductD3EOrder', 'p')
            ->where('p.deleted IS NULL');
        if ($status != null && !empty($status)) {
            $queryBuilder->andWhere('p.orderStatus = :status')
                ->setParameter('status', $status);
        }
        if ($dateStart != null && $dateEnd != null && !empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder->andWhere('p.dateCreation BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        $productD3EOrders = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Commandes D3E")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Secteur d\'activité')
            ->setCellValue('D1', 'Civilité')
            ->setCellValue('E1', 'Nom')
            ->setCellValue('F1', 'Prénom')
            ->setCellValue('G1', 'Email')
            ->setCellValue('H1', 'Adresse')
            ->setCellValue('I1', 'Code postal')
            ->setCellValue('J1', 'Ville')
            ->setCellValue('K1', 'Téléphone')
            ->setCellValue('L1', 'Statut')
            ->setCellValue('M1', 'Montant total')
            ->setCellValue('N1', 'Méthode de paiement')
            ->setCellValue('O1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Commandes D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productD3EOrders as $productD3EOrder) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productD3EOrder->getId())
                ->setCellValue('B' . $i, $productD3EOrder->getBusinessName())
                ->setCellValue('C' . $i, $productD3EOrder->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productD3EOrder->getCivility())
                ->setCellValue('E' . $i, $productD3EOrder->getLastName())
                ->setCellValue('F' . $i, $productD3EOrder->getFirstName())
                ->setCellValue('G' . $i, $productD3EOrder->getEmail())
                ->setCellValue('H' . $i, $productD3EOrder->getAddress())
                ->setCellValue('I' . $i, $productD3EOrder->getPostalCode())
                ->setCellValue('J' . $i, $productD3EOrder->getCity())
                ->setCellValue('K' . $i, $productD3EOrder->getPhone())
                ->setCellValue('L' . $i, $productD3EOrder->getOrderStatus())
                ->setCellValue('M' . $i, $numberManager->denormalize($productD3EOrder->getTotalAmount()))
                ->setCellValue('N' . $i, $productD3EOrder->getPaymentMethod())
                ->setCellValue('O' . $i, $productD3EOrder->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Commandes-D3E-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productD3EOrder/view/{id}", name="paprec_commercial_productD3EOrder_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws Exception
     */
    public function viewAction(Request $request, ProductD3EOrder $productD3EOrder)
    {
        $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');
        $productD3EOrderManager->isDeleted($productD3EOrder, true);

        $formAddInvoice = $this->createForm(ProductD3EOrderInvoiceType::class, $productD3EOrder);


        return $this->render('PaprecCommercialBundle:ProductD3EOrder:view.html.twig', array(
            'productD3EOrder' => $productD3EOrder,
            'formAddInvoice' => $formAddInvoice->createView()
        ));
    }

    /**
     * @Route("/productD3EOrder/edit/{id}", name="paprec_commercial_productD3EOrder_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws Exception
     */
    public function editAction(Request $request, ProductD3EOrder $productD3EOrder)
    {
        $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');
        $productD3EOrderManager->isDeleted($productD3EOrder, true);

        $status = array();
        foreach ($this->getParameter('paprec_order_status') as $s) {
            $status[$s] = $s;
        }

        $paymentMethods = array();
        foreach ($this->getParameter('paprec_order_payment_methods') as $p) {
            $paymentMethods[$p] = $p;
        }

        $form = $this->createForm(ProductD3EOrderType::class, $productD3EOrder, array(
            'status' => $status,
            'paymentMethods' => $paymentMethods
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3EOrder = $form->getData();
            $productD3EOrder->setDateUpdate(new \DateTime());

            if ($productD3EOrder->getAssociatedInvoice() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $associatedInvoice = $productD3EOrder->getAssociatedInvoice();
                $associatedInvoiceFileName = md5(uniqid()) . '.' . $associatedInvoice->guessExtension();

                $associatedInvoice->move($this->getParameter('paprec_commercial.product_d3e_order.files_path'), $associatedInvoiceFileName);

                $productD3EOrder->setAssociatedInvoice($associatedInvoiceFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
                'id' => $productD3EOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductD3EOrder:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3EOrder' => $productD3EOrder
        ));
    }

    /**
     * @Route("/productD3EOrder/remove/{id}", name="paprec_commercial_productD3EOrder_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws Exception
     */
    public function removeAction(Request $request, ProductD3EOrder $productD3EOrder)
    {
        $em = $this->getDoctrine()->getManager();

        if (!empty($productD3EOrder->getAssociatedInvoice())) {
            $this->removeFile($this->getParameter('paprec_commercial.product_d3e_order.files_path') . '/' . $productD3EOrder->getAssociatedInvoice());
            $productD3EOrder->setAssociatedInvoice();
        }

        $productD3EOrder->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productD3EOrder_index');
    }

    /**
     * @Route("/productD3EOrder/removeMany/{ids}", name="paprec_commercial_productD3EOrder_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
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
            $productD3EOrders = $em->getRepository('PaprecCommercialBundle:ProductD3EOrder')->findById($ids);
            foreach ($productD3EOrders as $productD3EOrder) {
                if (!empty($productD3EOrder->getAssociatedInvoice())) {
                    $this->removeFile($this->getParameter('paprec_commercial.product_d3e_order.files_path') . '/' . $productD3EOrder->getAssociatedInvoice());
                    $productD3EOrder->setAssociatedInvoice();
                }
                $productD3EOrder->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productD3EOrder_index');
    }

    /**
     * Suppression physique d'un fichier
     *
     * @param $path
     * @throws Exception
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
     * @Route("/productD3EOrder/{id}/addLine", name="paprec_commercial_productD3EOrder_addLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function addLineAction(Request $request, ProductD3EOrder $productD3EOrder)
    {

        $em = $this->getDoctrine()->getManager();


        if ($productD3EOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productD3EOrderLine = new ProductD3EOrderLine();

        $form = $this->createForm(ProductD3EOrderLineAddType::class, $productD3EOrderLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');

            $productD3EOrderLine = $form->getData();
            $productD3EOrderManager->addLine($productD3EOrder, $productD3EOrderLine);

            return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
                'id' => $productD3EOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductD3EOrderLine:add.html.twig', array(
            'form' => $form->createView(),
            'productD3EOrder' => $productD3EOrder,
        ));
    }

    /**
     * @Route("/productD3EOrder/{id}/editLine/{orderLineId}", name="paprec_commercial_productD3EOrder_editLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @ParamConverter("productD3EOrder", options={"id" = "id"})
     * @ParamConverter("productD3EOrderLine", options={"id" = "orderLineId"})
     */
    public function editLineAction(Request $request, ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {
        if ($productD3EOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EOrderLine->getProductD3EOrder() !== $productD3EOrder) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductD3EOrderLineEditType::class, $productD3EOrderLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');

            $productD3EOrderManager->editLine($productD3EOrder, $productD3EOrderLine);

            return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
                'id' => $productD3EOrder->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductD3EOrderLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3EOrder' => $productD3EOrder,
            'productD3EOrderLine' => $productD3EOrderLine
        ));
    }

    /**
     * @Route("/productD3EOrder/{id}/removeLine/{orderLineId}", name="paprec_commercial_productD3EOrder_removeLine")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @ParamConverter("productD3EOrder", options={"id" = "id"})
     * @ParamConverter("productD3EOrderLine", options={"id" = "orderLineId"})
     */
    public function removeLineAction(Request $request, ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {
        if ($productD3EOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productD3EOrderLine->getProductD3EOrder() !== $productD3EOrder) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productD3EOrderLine);
        $em->flush();

        $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');
        $total = $productD3EOrderManager->calculateTotal($productD3EOrder);
        $productD3EOrder->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
            'id' => $productD3EOrder->getId()
        ));
    }

    /**
     * @Route("/productD3EOrder/addInvoice/{id}", name="paprec_commercial_productD3EOrder_addInvoice")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws Exception
     */
    public function addInvoiceAction(Request $request, ProductD3EOrder $productD3EOrder)
    {

        $form = $this->createForm(ProductD3EOrderInvoiceType::class, $productD3EOrder);

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $productD3EOrder = $form->getData();
            $productD3EOrder->setDateUpdate(new \DateTime());

            if ($productD3EOrder->getAssociatedInvoice() instanceof UploadedFile) {
                $associatedInvoice = $productD3EOrder->getAssociatedInvoice();
                $associatedInvoiceFileName = md5(uniqid()) . '.' . $associatedInvoice->guessExtension();

                $associatedInvoice->move($this->getParameter('paprec_commercial.product_d3e_order.files_path'), $associatedInvoiceFileName);

                $productD3EOrder->setAssociatedInvoice($associatedInvoiceFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
                'id' => $productD3EOrder->getId()
            ));
        }
        return $this->render('PaprecCommercialBundle:ProductD3EOrder:view.html.twig', array(
            'productD3EOrder' => $productD3EOrder,
            'formAddInvoice' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3EOrder/{id}/downloadAssociatedInvoice", name="paprec_commercial_productD3EOrder_downloadAssociatedInvoice")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function downloadAssociatedInvoiceAction(ProductD3EOrder $productD3EOrder)
    {
        $filename = $productD3EOrder->getAssociatedInvoice();
        $path = $this->getParameter('paprec_commercial.product_d3e_order.files_path');
        $file = $path . '/' . $filename;
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $newFilename = "Commande-D3E-" . $productD3EOrder->getId() . '-Facture.' . $extension;

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

        // TODO : Erreur si !file_eistes($file)

    }

    /**
     * @Route("/productD3EOrder/{id}/sendAsssociatedInvoice", name="paprec_commercial_productD3EOrder_sendAssociatedInvoice")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws Exception
     */
    public function sendAssociatedInvoiceAction(ProductD3EOrder $productD3EOrder)
    {
        $productD3EOrderManager = $this->get('paprec_commercial.product_d3e_order_manager');
        $productD3EOrderManager->isDeleted($productD3EOrder, true);

        if ($productD3EOrder->getAssociatedInvoice() == null) {
            $this->get('session')->getFlashBag()->add('error', 'noUploadedInvoiceFound');
        } else {
            $sendInvoice = $productD3EOrderManager->sendAssociatedInvoiceMail($productD3EOrder);
            if($sendInvoice) {
                $this->get('session')->getFlashBag()->add('success', 'associatedInvoiceSent');
            } else {
                $this->get('session')->getFlashBag()->add('error', 'associatedInvoiceNotSent');
            }
        }
        return $this->redirectToRoute('paprec_commercial_productD3EOrder_view', array(
            'id' => $productD3EOrder->getId()
        ));
    }
}
