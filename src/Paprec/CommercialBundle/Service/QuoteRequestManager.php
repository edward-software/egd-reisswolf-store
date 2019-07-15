<?php

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use DoctrineExtensions\Query\Mysql\Date;
use Exception;
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Entity\QuoteRequestLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

            /**
             * Vérification que le quoteRequest existe ou ne soit pas supprimé
             */
            if ($quoteRequest === null || $this->isDeleted($quoteRequest)) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }


            return $quoteRequest;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le quoteRequest ce soit pas supprimé
     *
     * @param QuoteRequest $quoteRequestl
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(QuoteRequest $quoteRequest, $throwException = false)
    {
        $now = new \DateTime();

        if ($quoteRequest->getDeleted() !== null && $quoteRequest->getDeleted() instanceof \DateTime && $quoteRequest->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * Ajoute une quoteRequestLine à un quoteRequest
     * @param QuoteRequest $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param null $user
     * @throws Exception
     */
    public function addLine(QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine, $user = null)
    {

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:QuoteRequestLine')->findOneBy(
            array(
                'quoteRequest' => $quoteRequest,
                'product' => $quoteRequestLine->getProduct()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $quoteRequestLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $quoteRequestLine->setQuoteRequest($quoteRequest);
            $quoteRequest->addQuoteRequestLine($quoteRequestLine);

            $quoteRequestLine->setRentalUnitPrice($quoteRequestLine->getProduct()->getRentalUnitPrice());
            $quoteRequestLine->setTransportUnitPrice($quoteRequestLine->getProduct()->getTransportUnitPrice());
            $quoteRequestLine->setTreatmentUnitPrice($quoteRequestLine->getProduct()->getTreatmentUnitPrice());
            $quoteRequestLine->setTraceabilityUnitPrice($quoteRequestLine->getProduct()->getTraceabilityUnitPrice());
            $quoteRequestLine->setProductName($quoteRequestLine->getProduct()->getId());

            $this->em->persist($quoteRequestLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($quoteRequestLine);
            $quoteRequestLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        $quoteRequest->setDateUpdate(new \DateTime());
        $quoteRequest->setUserUpdate($user);
        $this->em->flush();
    }


    /**
     * Pour ajouter une QuoteRequestLine depuis le Cart, il faut d'abord retrouver le Product
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(QuoteRequest $quoteRequest, $productId, $qtty)
    {
        $productManager = $this->container->get('paprec_catalog.product_manager');

        try {
            $product = $productManager->get($productId);
            $quoteRequestLine = new QuoteRequestLine();

            $quoteRequestLine->setProduct($product);
            $quoteRequestLine->setQuantity($qtty);
            $this->addLine($quoteRequest, $quoteRequestLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param QuoteRequest $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param $user
     * @throws Exception
     */
    public function editLine(QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine, $user)
    {
        $now = new \DateTime();

        $totalLine = $this->calculateTotalLine($quoteRequestLine);
        $quoteRequestLine->setTotalAmount($totalLine);
        $quoteRequestLine->setDateUpdate($now);

        $total = $this->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        $quoteRequest->setDateUpdate($now);
        $quoteRequest->setUserUpdate($user);

        $this->em->flush();
    }

    /**
     * Retourne le montant total d'une QuoteRequestLine
     * @param QuoteRequestLine $quoteRequestLine
     * @return float|int
     */
    public function calculateTotalLine(QuoteRequestLine $quoteRequestLine)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');
        $productManager = $this->container->get('paprec_catalog.product_manager');

        return $numberManager->normalize(
            $productManager->calculatePrice(
                $quoteRequestLine->getQuoteRequest()->getPostalCode(),
                $quoteRequestLine->getProduct(),
                $quoteRequestLine->getQuantity()
            )
        );
    }

    /**
     * Calcule le montant total d'un QuoteRequest
     * @param QuoteRequest $quoteRequest
     * @return float|int
     */
    public function calculateTotal(QuoteRequest $quoteRequest)
    {
        $totalAmount = 0;
        if ($quoteRequest->getQuoteRequestLines() && count($quoteRequest->getQuoteRequestLines())) {
            foreach ($quoteRequest->getQuoteRequestLines() as $quoteRequestLine) {
                $totalAmount += $this->calculateTotalLine($quoteRequestLine);
            }
        }
        return $totalAmount;
    }


    /**
     * Envoie un mail à la personne ayant fait une demande de devis
     * @throws Exception
     */
    public function sendConfirmRequestEmail(QuoteRequest $quoteRequest, $locale)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $this->get($quoteRequest);

            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $message = \Swift_Message::newInstance()
                ->setSubject('Reisswolf E-shop : Votre demande de devis' . ' N°' . $quoteRequest->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/confirmQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Envoie un mail au commercial associé lui indiquant la nouvelle demande de devis créée
     * @throws Exception
     */
    public function sendnewRequestEmail(QuoteRequest $quoteRequest, $locale)
    {

        try {
            $from = $this->container->getParameter('paprec_email_sender');
            $this->get($quoteRequest);

            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $message = \Swift_Message::newInstance()
                ->setSubject('Reisswolf E-shop : Votre demande de devis' . ' N°' . $quoteRequest->getId())
                ->setFrom($from)
                ->setTo($rcptTo)
                ->setBody(
                    $this->container->get('templating')->render(
                        '@PaprecCommercial/QuoteRequest/emails/newQuoteEmail.html.twig',
                        array(
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        )
                    ),
                    'text/html'
                );
            if ($this->container->get('mailer')->send($message)) {
                return true;
            }
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
