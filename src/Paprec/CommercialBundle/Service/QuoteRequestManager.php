<?php

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
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
     */
    public function addLine(QuoteRequest $quoteRequest, QuoteRequestLine $quoteRequestLine)
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
        foreach ($quoteRequest->getQuoteRequestLines() as $quoteRequestLine) {
            $totalAmount += $this->calculateTotalLine($quoteRequestLine);
        }
        return $totalAmount;
    }

}
