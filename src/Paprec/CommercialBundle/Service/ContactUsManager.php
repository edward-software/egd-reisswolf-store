<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 30/11/2018
 * Time: 17:14
 */

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CommercialBundle\Entity\ContactUs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactUsManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($contactUs)
    {
        $id = $contactUs;
        if ($contactUs instanceof ContactUs) {
            $id = $contactUs->getId();
        }
        try {

            $contactUs = $this->em->getRepository('PaprecCommercialBundle:ContactUs')->find($id);

            if ($contactUs === null || $this->isDeleted($contactUs)) {
                throw new EntityNotFoundException('contactUsNotFound');
            }

            return $contactUs;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de contact ne soit pas supprimée
     *
     * @param ContactUs $contactUs
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ContactUs $contactUs, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($contactUs->getDeleted() !== null && $contactUs->getDeleted() instanceof \DateTime && $contactUs->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('contactUsNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Envoie un mail à la personne ayant fait une demande de contact
     * @param ContactUs $contactUs
     * @throws Exception
     */
    public function sendConfirmRequestEmail(ContactUs $contactUs)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $contactUs->getEmail();

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre demande de contact N°' . $contactUs->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ContactUs/emails/sendConfirmRequestEmail.html.twig',
                        array(
                            'contactUs' => $contactUs
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmContactUs', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }



    /**
     * Envoie un mail à l'assistant de la direction commerciale avec les données du formulaire de demande de contact
     *
     * @param ContactUs $contactUs
     * @return bool
     * @throws Exception
     */
    public function sendNewRequestEmail(ContactUs $contactUs)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_assistant_commercial_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle demande de contact N°' . $contactUs->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/ContactUs/emails/sendNewRequestEmail.html.twig',
                        array(
                            'contactUs' => $contactUs
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewContactUs', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}