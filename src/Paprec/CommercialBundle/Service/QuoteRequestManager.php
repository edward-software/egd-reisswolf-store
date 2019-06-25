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
use iio\libmergepdf\Merger;
use Knp\Snappy\Pdf;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class QuoteRequestManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($quoteRequest)
    {
        $id = $quoteRequest;
        if ($quoteRequest instanceof QuoteRequest) {
            $id = $quoteRequest->getId();
        }
        try {

            $quoteRequest = $this->em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($id);

            if ($quoteRequest === null || $this->isDeleted($quoteRequest)) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return $quoteRequest;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de devis ne soit pas supprimée
     *
     * @param QuoteRequest $quoteRequest
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(QuoteRequest $quoteRequest, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($quoteRequest->getDeleted() !== null && $quoteRequest->getDeleted() instanceof \DateTime && $quoteRequest->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Envoie un mail à l'assistant au responsable de la division concernée avec les données du formulaire de prestation régulière
     *
     * @param QuoteRequest $quoteRequest
     * @return bool
     * @throws Exception
     */
    public function sendNewRequestEmail(QuoteRequest $quoteRequest)
    {
        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $rcptTo = $this->container->getParameter('paprec_manager_'.strtolower($quoteRequest->getDivision()).'_email');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Nouvelle demande de prestation régulière ' . $quoteRequest->getDivision() .' N°' . $quoteRequest->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/sendNewRequestEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest
                        )
                    ),
                    'text/html'
                );

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

    /**
     * Envoi à l'internante du devis uploadé par le manager
     *
     * @param QuoteRequest $quoteRequest
     * @return bool
     * @throws Exception
     */
    public function sendAssociatedQuoteMail(QuoteRequest $quoteRequest) {
        try {
            $from = $this->container->getParameter('paprec_email_sender');

            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-EasyRecyclage-Devis-' . $quoteRequest->getId() . '.pdf';

            if ($quoteRequest->getAssociatedQuote()) {
                $filename = $quoteRequest->getAssociatedQuote();
                $path = $this->container->getParameter('paprec_commercial.quote_request.files_path');
                $pdfFile = $path . '/' . $filename;
            } else {
                return false;
            }

            $attachment = \Swift_Attachment::newInstance(file_get_contents($pdfFile), $pdfFilename, 'application/pdf');

            $message = \Swift_Message::newInstance()
                ->setSubject('Easy-Recyclage : Votre devis pour prestation régulière')
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/sendAssociatedQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest
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