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
use Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuoteRequestNonCorporateManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($quoteRequestNonCorporate)
    {
        $id = $quoteRequestNonCorporate;
        if ($quoteRequestNonCorporate instanceof QuoteRequestNonCorporate) {
            $id = $quoteRequestNonCorporate->getId();
        }
        try {

            $quoteRequestNonCorporate = $this->em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->find($id);

            if ($quoteRequestNonCorporate === null || $this->isDeleted($quoteRequestNonCorporate)) {
                throw new EntityNotFoundException('quoteRequestNonCorporateNotFound');
            }

            return $quoteRequestNonCorporate;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de devis non entreprise ne soit pas supprimée
     *
     * @param QuoteRequestNonCorporate $quoteRequestNonCorporate
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(QuoteRequestNonCorporate $quoteRequestNonCorporate, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($quoteRequestNonCorporate->getDeleted() !== null && $quoteRequestNonCorporate->getDeleted() instanceof \DateTime && $quoteRequestNonCorporate->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNonCorporateNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Envoie un mail à la personne ayant fait une demande de devis Non Entreprise avec le résumé de son besoin
     * @param QuoteRequestNonCorporate $quoteRequestNonCorporate
     * @throws Exception
     */
    public function sendConfirmRequestEmail(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $quoteRequestNonCorporate->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre demande de devis ' . $quoteRequestNonCorporate->getCustomerType() . ' N°' . $quoteRequestNonCorporate->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequestNonCorporate/emails/sendConfirmRequestEmail.html.twig',
                        array(
                            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmQuoteRequestNonCorporate', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoie un mail à l'assistant de la direction commerciale avec les données du formulaire de demande de devis Non Entreprise
     *
     * @param QuoteRequestNonCorporate $quoteRequestNonCorporate
     * @return bool
     * @throws Exception
     */
    public function sendNewRequestEmail(QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_assistant_commercial_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle demande de devis : ' . $quoteRequestNonCorporate->getCustomerType() . ' ' . $quoteRequestNonCorporate->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequestNonCorporate/emails/sendNewRequestEmail.html.twig',
                        array(
                            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
                        )
                    ),
                    'text/html'
                );

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewQuoteRequestNonCorporate', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Envoi à l'internante du devis uploadé par le manager
     *
     * @param QuoteRequestNonCorporate $quoteRequestNonCorporate
     * @return bool
     * @throws Exception
     */
    public function sendAssociatedQuoteMail(QuoteRequestNonCorporate $quoteRequestNonCorporate) {
        try {
            $from = $this->container->getParameter('paprec_email_sender');

            $rcptTo = $quoteRequestNonCorporate->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Devis-' . $quoteRequestNonCorporate->getId() . '.pdf';

            if ($quoteRequestNonCorporate->getAssociatedQuote()) {
                $filename = $quoteRequestNonCorporate->getAssociatedQuote();
                $path = $this->container->getParameter('paprec_commercial.quote_request.files_path');
                $pdfFile = $path . '/' . $filename;
            } else {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre devis')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequestNonCorporate/emails/sendAssociatedQuoteEmail.html.twig',
                        array(
                            'quoteRequestNonCorporate' => $quoteRequestNonCorporate
                        )
                    ),
                    'text/html'
                )
                ->attach($attachment);

            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendNewQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}