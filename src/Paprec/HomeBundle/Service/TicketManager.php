<?php

namespace Paprec\HomeBundle\Service;

use Paprec\HomeBundle\Entity\Workspace;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\ORMException;
use \Exception;

use Paprec\UserBundle\Entity\User;
use Paprec\HomeBundle\Entity\Ticket;
use Paprec\HomeBundle\Entity\UserWorkspace;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketManager
{

    private $em;
    private $container;

    public function __construct($em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getLevels($translate = false)
    {
        if($translate) {
            $translator = $this->container->get('translator');
        }
        $tmp = array();
        foreach ($this->container->getParameter('paprec_home.ticket.levels') as $level) {
            $tmp[$level] = $level;
            if($translate) {
                $tmp[$level] = $translator->trans('Home.Ticket.Level.' . $level);
            }
        }
        return $tmp;
    }

    public function isValidLevel($level)
    {
        if (in_array($level, $this->getLevels())) {
            return true;
        }
        return false;
    }

    public function getStatus($translate = false)
    {
        if($translate) {
            $translator = $this->container->get('translator');
        }
        $tmp = array();
        foreach ($this->container->getParameter('paprec_home.ticket.status') as $status) {
            $tmp[$status] = $status;
            if($translate) {
                $tmp[$status] = $translator->trans('Home.Ticket.Status.' . $status);
            }
        }
        return $tmp;
    }

    public function isValidStatus($status)
    {
        if (in_array($status, $this->getStatus())) {
            return true;
        }
        return false;
    }

    public function getInvoiceStatus($translate = false)
    {
        if($translate) {
            $translator = $this->container->get('translator');
        }
        $tmp = array();
        foreach ($this->container->getParameter('paprec_home.ticket.invoice_status') as $invoiceStatus) {
            $tmp[$invoiceStatus] = $invoiceStatus;
            if($translate) {
                $tmp[$invoiceStatus] = $translator->trans('Home.Ticket.Invoice-status.' . $invoiceStatus);
            }
        }
        return $tmp;
    }

    public function isValidInvoiceStatus($invoiceStatus)
    {
        if (in_array($invoiceStatus, $this->getInvoiceStatus())) {
            return true;
        }
        return false;
    }

    public function sendNewTicketNotification(Ticket $ticket, User $systemUser)
    {

        $recipient = $this->container->getParameter('paprec_home.ticket.recipient');

        $message = \Swift_Message::newInstance()
            ->setSubject('Workspace Support : New Ticket - ' . $ticket->getLevel() . ' - ' . $ticket->getStatus())
            ->setFrom($this->container->getParameter('paprec_email_sender'))
            ->setTo($recipient)
            ->setBody($this->container->get('templating')->render('@PaprecHome/Admin/Ticket/emails/newTicketNotificationEmail.html.twig', array(
                'ticket' => $ticket
            )), 'text/html');

        $this->container->get('mailer')->send($message);
    }

    public function sendUpdateTicketNotification(Ticket $ticket, User $systemUser, $status = null)
    {

        /**
         * Génération de la liste des e-mail de destinataires :
         * L'admin Support, Le créateur du ticket, le modificateur et les créateurs et modificateurs des messages
         */
        $recipients = array();
        $recipients[] = $this->container->getParameter('paprec_home.ticket.recipient');
        $recipients[] = $ticket->getUserCreation()->getEmail();
        if ($ticket->getUserUpdate()) {
            $recipients[] = $ticket->getUserUpdate()->getEmail();
        }
        foreach ($ticket->getTicketMessages() as $ticketMessage) {
            $recipients[] = $ticketMessage->getUserCreation()->getEmail();
            if ($ticketMessage->getUserUpdate()) {
                $recipients[] = $ticketMessage->getUserUpdate()->getEmail();
            }
        }
        $recipients = array_unique($recipients);

        if ($status) {

            $message = \Swift_Message::newInstance()
                ->setSubject('Workspace Support : Ticket Update (Status change) - ' . $ticket->getLevel() . ' - ' . $ticket->getStatus())
                ->setFrom($this->container->getParameter('paprec_email_sender'))
                ->setTo($recipients)
                ->setBody($this->container->get('templating')->render('@PaprecHome/Admin/Ticket/emails/updateTicketNotificationEmail.html.twig', array(
                    'ticket' => $ticket
                )), 'text/html');

        } else {

            $message = \Swift_Message::newInstance()
                ->setSubject('Workspace Support : Ticket Update - ' . $ticket->getLevel() . ' - ' . $ticket->getStatus())
                ->setFrom($this->container->getParameter('paprec_email_sender'))
                ->setTo($recipients)
                ->setBody($this->container->get('templating')->render('@PaprecHome/Admin/Ticket/emails/updateTicketNotificationEmail.html.twig', array(
                    'ticket' => $ticket
                )), 'text/html');
        }


        $this->container->get('mailer')->send($message);
    }

    public function newTicketAssignementNotification(Ticket $ticket, User $systemUser)
    {

        $recipient = $ticket->getUserInCharge()->getEmail();

        $message = \Swift_Message::newInstance()
            ->setSubject('Workspace Support : Ticket Assignement - ' . $ticket->getLevel() . ' - ' . $ticket->getStatus())
            ->setFrom($this->container->getParameter('paprec_email_sender'))
            ->setTo($recipient)
            ->setBody($this->container->get('templating')->render('@PaprecHome/Admin/Ticket/emails/newTicketAssignementNotificationEmail.html.twig', array(
                'ticket' => $ticket
            )), 'text/html');


        $this->container->get('mailer')->send($message);
    }

    public function updateTicketAssignementNotification(Ticket $ticket, User $systemUser)
    {

        $recipient = $ticket->getUserInCharge()->getEmail();

        $message = \Swift_Message::newInstance()
            ->setSubject('Workspace Support : Ticket Assignement - ' . $ticket->getLevel() . ' - ' . $ticket->getStatus())
            ->setFrom($this->container->getParameter('paprec_email_sender'))
            ->setTo($recipient)
            ->setBody($this->container->get('templating')->render('@PaprecHome/Admin/Ticket/emails/newTicketAssignementNotificationEmail.html.twig', array(
                'ticket' => $ticket
            )), 'text/html');


        $this->container->get('mailer')->send($message);
    }

}
