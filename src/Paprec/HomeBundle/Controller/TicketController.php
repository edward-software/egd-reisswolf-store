<?php

namespace Paprec\HomeBundle\Controller;

use Exception;
use Paprec\HomeBundle\Entity\Ticket;
use Paprec\HomeBundle\Entity\TicketFile;
use Paprec\HomeBundle\Entity\TicketMessage;
use Paprec\HomeBundle\Entity\TicketStatus;
use Paprec\HomeBundle\Form\TicketMessageType;
use Paprec\HomeBundle\Form\TicketType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketController extends Controller
{
    /**
     * @Route("/ticket", name="paprec_home_ticket_index")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $ticketManager = $this->get('paprec_home.ticket');
        $translator = $this->get('translator');

        /**
         * Récupération de la liste des status
         */
        $status = $ticketManager->getStatus();
        $tmp = array();
        foreach ($status as $status1 => $value) {
            $tmp[] = array(
                'value' => $status1,
                'text' => $translator->trans('Home.Ticket.Status.' . $value)
            );
        }
        $status = $tmp;

        return $this->render('PaprecHomeBundle:Ticket:index.html.twig', array(
            'status' => $status
        ));
    }

    /**
     * @Route("/ticket/loadList", name="paprec_home_ticket_loadList", condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     */
    public function loadListAction(Request $request)
    {

        $translator = $this->container->get('translator');
        $notStatus = $request->get('notStatus');
        $notLevel = $request->get('notLevel');
        $level = $request->get('level');
        $status = $request->get('status');
        $invoiceStatus = $request->get('invoiceStatus');

        if ($notStatus) {
            $notStatus = explode(',', $notStatus);
        }
        if ($notLevel) {
            $notLevel = explode(',', $notLevel);
        }

        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 't.id', 'method' => array('getId'));
        $cols['title'] = array('label' => 'title', 'id' => 't.title', 'method' => array('getTitle'));
        $cols['level'] = array('label' => 'level', 'id' => 't.level', 'method' => array('getLevel'));
        $cols['status'] = array('label' => 'status', 'id' => 't.status', 'method' => array('getStatus'));
        $cols['invoiceStatus'] = array('label' => 'invoiceStatus', 'id' => 't.invoiceStatus', 'method' => array('getInvoiceStatus'));
        $cols['authorName'] = array('label' => 'authorName', 'id' => 'u.username', 'method' => array('getUserCreation', 'getUsername'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 't.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('t', 'u'))
            ->from('PaprecHomeBundle:Ticket', 't')
            ->leftJoin('t.userCreation', 'u')
            ->where('t.deleted is NULL');

        if (!empty($notStatus) && is_array($notStatus) && count($notStatus)) {
            $queryBuilder
                ->andWhere('t.status NOT IN (:notStatus)')
                ->setParameter('notStatus', $notStatus);

        }
        if (!empty($notLevel) && is_array($notLevel) && count($notLevel)) {
            $queryBuilder
                ->andWhere('t.level NOT IN (:notLevel)')
                ->setParameter('notLevel', $notLevel);

        }
        if ($level) {
            $queryBuilder
                ->andWhere('t.level = :level')
                ->setParameter('level', $level);

        }
        if ($status) {
            $queryBuilder
                ->andWhere('t.status = :status')
                ->setParameter('status', $status);

        }
        if ($invoiceStatus) {
            $queryBuilder
                ->andWhere('t.invoiceStatus = :invoiceStatus')
                ->setParameter('invoiceStatus', $invoiceStatus);

        }


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('t.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('t.title', '?1'),
                    $queryBuilder->expr()->like('t.content', '?1'),
                    $queryBuilder->expr()->like('u.username', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');

            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

//        $tmp = array();
//        foreach ($datatable['data'] as $row) {
//            $line = $row;
//
//            $line['status'] = $translator->trans('Home.Ticket.Status.' . $row['status']);
//            $line['level'] = $translator->trans('Home.Ticket.Level.' . $row['level']);
//            $line['invoiceStatus'] = $translator->trans('Home.Ticket.Invoice-status.' . $row['invoiceStatus']);
//
//            $tmp[] = $line;
//        }
//
//        $datatable['data'] = $tmp;

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/ticket/view/{id}", name="paprec_home_ticket_view")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function viewAction(Request $request, Ticket $ticket)
    {
        $systemUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $this->isDeleted($ticket);

        $path = array();

        /**
         * Retourne les url(s) des fichiers uploader lié à notre ticket
         */
        if (!empty($ticket->getTicketFiles())) {
            foreach ($ticket->getTicketFiles() as $ticketFile) {
                $path = $ticketFile->getPath();

            }
        }


        /**
         * Récupération de la liste des messages, par ordre chronologique décroissant
         */

        $tiketMessages = $em->getRepository('PaprecHomeBundle:TicketMessage')->findBy(array(
            'ticket' => $ticket,
            'deleted' => null,
        ), array(
            'dateCreation' => 'DESC'
        ));

        return $this->render('PaprecHomeBundle:Ticket:view.html.twig', array(
            'ticket' => $ticket,
            'ticketMessages' => $tiketMessages
        ));

    }

    /**
     * @Route("/ticket/add", name="paprec_home_ticket_add")
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request)
    {
        $systemUser = $this->getUser();
        $ticketManager = $this->get('paprec_home.ticket');
        $dataDirectory = $this->getParameter('paprec_home.ticket_file.general_path');

        $ticket = new Ticket();

        $form = $this->createForm(TicketType::class, $ticket, array(
            'levels' => $ticketManager->getLevels(true),
            'status' => $ticketManager->getStatus(true),
            'invoiceStatus' => $ticketManager->getInvoiceStatus(true)
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticket = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $ticket->setUserCreation($systemUser);
            $em->persist($ticket);
            $em->flush();

            if (!empty($form["ticketFile"]->getData())) {


                foreach ($form["ticketFile"]->getData() as $uploadedFile) {
                    $ticketFile = new TicketFile();
                    $ticketFile->setUserCreation($systemUser);
                    $ticketFile->setTicket($ticket);

                    $em->persist($ticketFile);

                    $extension = $uploadedFile->guessClientExtension();
                    $fileName = md5(uniqid()) . '.' . $extension;

                    $ticketFile->setPath($fileName);

                    $uploadedFile->move($dataDirectory, $fileName);

                    if (!file_exists($dataDirectory . '/' . $ticketFile->getPath())) {
                        throw new Exception('unableToCheckFile', 500);
                    }
                }

            }

            $ticketStatus = new TicketStatus();
            $em->persist($ticketStatus);
            $ticketStatus->setUserCreation($systemUser);
            $ticketStatus->setTicket($ticket);
            $ticketStatus->setStatus($ticket->getStatus());

            $em->flush();

            return $this->redirectToRoute('paprec_home_ticket_view', array(
                'id' => $ticket->getId()
            ));

        }

        return $this->render('PaprecHomeBundle:Ticket:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/ticket/edit/{id}", name="paprec_home_ticket_edit")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function editAction(Request $request, Ticket $ticket)
    {

        $systemUser = $this->getUser();
        $ticketManager = $this->get('paprec_home.ticket');
        $dataDirectory = $this->getParameter('paprec_home.ticket_file.general_path');

        $this->isDeleted($ticket);

        $actualTicketStatus = $ticket->getStatus();

        $form = $this->createForm(TicketType::class, $ticket, array(
            'levels' => $ticketManager->getLevels(true),
            'status' => $ticketManager->getStatus(true),
            'invoiceStatus' => $ticketManager->getInvoiceStatus(true)
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticket = $form->getData();

            $ticket->setDateUpdate(new \DateTime);
            $ticket->setUserUpdate($systemUser);

            $em = $this->getDoctrine()->getManager();

            if (!empty($form["ticketFile"]->getData())) {


                foreach ($form["ticketFile"]->getData() as $uploadedFile) {
                    $ticketFile = new TicketFile();
                    $ticketFile->setUserCreation($systemUser);
                    $ticketFile->setTicket($ticket);

                    $em->persist($ticketFile);

                    $extension = $uploadedFile->guessClientExtension();
                    $fileName = md5(uniqid()) . '.' . $extension;

                    $ticketFile->setPath($fileName);

                    $uploadedFile->move($dataDirectory, $fileName);

                    if (!file_exists($dataDirectory . '/' . $ticketFile->getPath())) {
                        throw new Exception('unableToCheckFile', 500);
                    }
                }

            }
            if ($form["status"]->getData() != $actualTicketStatus) {
                $ticketStatus = new TicketStatus();
                $em->persist($ticketStatus);
                $ticketStatus->setUserCreation($systemUser);
                $ticketStatus->setTicket($ticket);
                $ticketStatus->setStatus($ticket->getStatus());

            }
            $em->flush();

            return $this->redirectToRoute('paprec_home_ticket_view', array(
                'id' => $ticket->getId()
            ));

        }

        return $this->render('PaprecHomeBundle:Ticket:edit.html.twig', array(
            'form' => $form->createView(),
            'ticket' => $ticket
        ));
    }

    /**
     * @Route("/ticket/remove/{id}", name="paprec_home_ticket_remove")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function removeAction(Request $request, Ticket $ticket, $doFlush = true)
    {
        $systemUser = $this->getUser();

        $this->isDeleted($ticket);

        $json = $request->get('json');

        $em = $this->getDoctrine()->getManager();

        $ticketMessages = $ticket->getTicketMessages();

        if ($ticketMessages) {
            foreach ($ticketMessages as $ticketMessage) {
                $this->forward('PaprecHomeBundle:Admin/TicketMessage:remove', array(
                    'request' => $request,
                    'ticketMessage' => $ticketMessage,
                    'doFlush' => false
                ));
            }
        }

        $ticket->setDeleted(new \DateTime);
        $ticket->setUserUpdate($systemUser);

        if ($doFlush) {
            $em->flush();
        }


        if ($json) {
            return new JsonResponse(array(
                'resultCode' => 1,
                'resultMessage' => 'success'
            ));
        }

        return $this->redirectToRoute('paprec_home_ticket_index');
    }

    /**
     * @Route("/ticket/duplicate/{id}", name="paprec_home_ticket_duplicate")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function duplicateAction(Request $request, Ticket $ticket)
    {

        $systemUser = $this->getUser();
        $ticketManager = $this->get('paprec_home.ticket');

        $this->isDeleted($ticket);

        $newTicket = clone $ticket;

        $form = $this->createForm(TicketType::class, $newTicket, array(
            'levels' => $ticketManager->getLevels(),
            'status' => $ticketManager->getStatus(),
            'invoiceStatus' => $ticketManager->getInvoiceStatus()
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newTicket = $form->getData();
            $newTicket->setDateCreation(new \DateTime);
            $newTicket->setUserCreation($systemUser);
            $newTicket->setDateUpdate(null);
            $newTicket->setUserUpdate(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newTicket);
            $em->flush();

            return $this->redirectToRoute('paprec_home_ticket_view', array(
                'id' => $newTicket->getId()
            ));

        }

        return $this->render('PaprecHomeBundle:Ticket:edit.html.twig', array(
            'form' => $form->createView(),
            'ticket' => $newTicket
        ));
    }


    /**
     * @Route("/ticket/removeMany/{ids}", name="paprec_home_ticket_removeMany")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');
        $json = $request->get('json');
        $systemUser = $this->getUser();

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $tickets = $em->getRepository('PaprecHomeBundle:Ticket')->findById($ids);
            foreach ($tickets as $ticket) {

                $this->isDeleted($ticket);

                if ($json) {
                    return new JsonResponse(array(
                        'resultCode' => 0,
                        'resultMessage' => 'notAuthorized'
                    ));
                }


                $ticket->setDeleted(new \DateTime);
                $ticket->setUserUpdate($systemUser);
            }
            $em->flush();

            if ($json) {
                return new JsonResponse(array(
                    'resultCode' => 1,
                    'resultMessage' => 'success'
                ));
            }

            return $this->redirectToRoute('paprec_home_ticket_index');
        }

        if ($json) {
            return new JsonResponse(array(
                'resultCode' => 0,
                'resultMessage' => 'error'
            ));
        }

        return $this->redirectToRoute('paprec_home_ticket_index');
    }

    /***************************************************************************************************************************************
     * Ticket File
     */

    /**
     * @param Request $request
     * @param TicketFile $ticketFile
     * @return Response
     *
     * @Route("/ticket/ticketFile/{id}", name="paprec_home_ticket_ticketFile_download")
     * @Security("has_role('ROLE_USER')")
     */
    public function downloadAction(Request $request, TicketFile $ticketFile)
    {
        $dataDirectory = $this->getParameter('paprec_home.ticket_file.general_path');
        $content = file_get_contents($dataDirectory . '/' . $ticketFile->getPath());
        $fileName = basename($ticketFile->getPath());

        $response = new Response();

        $response->headers->set('Content-Disposition', 'attachment;filename=' . $fileName);

        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/ticket/{id}/ticketFile/remove/{ticketFileId}", name="paprec_home_ticket_ticketFile_remove")
     * @Security("has_role('ROLE_USER')")
     */
    public function removeTicketFileAction(Request $request, Ticket $ticket, $doFlush = true)
    {
        $systemUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PaprecHomeBundle:TicketFile');
        $ticketFile = $repository->find($request->get('ticketFileId'));


        $this->isDeletedTicketFile($ticketFile);

        $json = $request->get('json');

        $systemUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $ticketFile->setDeleted(new \DateTime);
        $ticketFile->setUserUpdate($systemUser);

        if ($doFlush) {
            $em->flush();
        }

        if ($json) {
            return new JsonResponse(array(
                'resultCode' => 1,
                'resultMessage' => 'success'
            ));
        }

        return $this->redirectToRoute('paprec_home_ticket_view', array(
            'id' => $ticket->getId()
        ));
    }


    /***************************************************************************************************************************************
     * Ticket Message
     */

    /**
     * @Route("/ticket/{id}/addTicketMessage", name="paprec_home_ticket_addTicketMessage")
     * @Security("has_role('ROLE_USER')")
     */
    public function addTicketMessageAction(Request $request, Ticket $ticket)
    {

        $em = $this->getDoctrine()->getManager();
        $systemUser = $this->getUser();
        $ticketManager = $this->get('paprec_home.ticket');
        $this->isDeleted($ticket);

        $ticketMessage = new TicketMessage();

        $form = $this->createForm(TicketMessageType::class, $ticketMessage, array(
            'action' => $this->generateUrl('paprec_home_ticket_addTicketMessage', array('id' => $ticket->getId()))
        ));


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticketMessage = $form->getData();
            $ticketMessage->setTicket($ticket);
            $ticketMessage->setUserCreation($systemUser);

            $em->persist($ticketMessage);
            $em->flush();

            return $this->redirectToRoute('paprec_home_ticket_view', array(
                'id' => $ticket->getId()
            ));

        }

        return $this->render('PaprecHomeBundle:Ticket:addTicketMessage.html.twig', array(
            'form' => $form->createView(),
            'ticket' => $ticket
        ));
    }


    /**
     * @Route("/ticket/{id}/editTicketMessage/{messageId}", name="paprec_home_ticket_editTicketMessage")
     * @Security("has_role('ROLE_USER')")
     * @throws Exception
     */
    public function editTicketMessageAction(Request $request, Ticket $ticket)
    {
        $em = $this->getDoctrine()->getManager();
        $systemUser = $this->getUser();
        $ticketManager = $this->get('paprec_home.ticket');
        $this->isDeleted($ticket);

        /**
         * recupère l'instance TicketMessage
         */
        $repository = $em->getRepository('PaprecHomeBundle:TicketMessage');
        $ticketMessage = $repository->find($request->get('messageId'));

        $form = $this->createForm(TicketMessageType::class, $ticketMessage, array(
            'action' => $this->generateUrl('paprec_home_ticket_editTicketMessage', array('id' => $ticket->getId(), 'messageId' => $ticketMessage->getId()))
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticketMessage = $form->getData();
            $ticketMessage->setDateUpdate(new \DateTime);
            $ticketMessage->setUserUpdate($systemUser);

            $em->flush();

            return $this->redirectToRoute('paprec_home_ticket_view', array(
                'id' => $ticket->getId()
            ));

        }

        return $this->render('PaprecHomeBundle:Ticket:editTicketMessage.html.twig', array(
            'form' => $form->createView(),
            'ticket' => $ticket
        ));
    }


    /**************************************************************************************************************************************************************************************************
     *
     * Methodes privées
     *
     */

    /**
     * Verifie si le ticket est supprimé ou pas
     */
    private function isDeleted(Ticket $ticket)
    {
        try {
            if (!empty($ticket->getDeleted())) {
                throw new Exception('ticketNotFound', 404);
            }

            return;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Verifie si le ticket est supprimé ou pas
     */
    private function isDeletedTicketFile(TicketFile $ticketFile)
    {
        try {
            if (!empty($ticketFile->getDeleted())) {
                throw new Exception('ticketFileNotFound', 404);
            }

            return;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


}