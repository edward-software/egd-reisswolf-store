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
use Paprec\CommercialBundle\Entity\CallBack;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CallBackManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($callBack)
    {
        $id = $callBack;
        if ($callBack instanceof CallBack) {
            $id = $callBack->getId();
        }
        try {

            $callBack = $this->em->getRepository('PaprecCommercialBundle:CallBack')->find($id);

            if ($callBack === null || $this->isDeleted($callBack)) {
                throw new EntityNotFoundException('callBackNotFound');
            }

            return $callBack;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de rappel ne soit pas supprimée
     *
     * @param CallBack $callBack
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(CallBack $callBack, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($callBack->getDeleted() !== null && $callBack->getDeleted() instanceof \DateTime && $callBack->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('callBackNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Envoie un mail à la personne ayant fait une demande de rappel
     *
     * @param CallBack $callBack
     * @throws Exception
     */
    public function sendConfirmRequestEmail(CallBack $callBack)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $callBack->getEmail();

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre demande de rappel N°' . $callBack->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/CallBack/emails/sendConfirmRequestEmail.html.twig',
                        array(
                            'callBack' => $callBack
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmCallBack', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Envoie un mail à l'assistant de la direction commerciale avec les données du formulaire de demande de rappel
     *
     * @param CallBack $callBack
     * @return $callBack
     * @throws Exception
     */
    public function sendNewRequestEmail(CallBack $callBack)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_assistant_commercial_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle demande de rappel N°' . $callBack->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/CallBack/emails/sendNewRequestEmail.html.twig',
                        array(
                            'callBack' => $callBack
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewCallBack', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}