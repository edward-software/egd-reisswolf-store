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
use Exception;
use Paprec\CommercialBundle\Entity\BusinessLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BusinessLineManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($businessLine)
    {
        $id = $businessLine;
        if ($businessLine instanceof BusinessLine) {
            $id = $businessLine->getId();
        }
        try {

            $businessLine = $this->em->getRepository('PaprecCommercialBundle:BusinessLine')->find($id);

            if ($businessLine === null || $this->isDeleted($businessLine)) {
                throw new EntityNotFoundException('businessLineNotFound');
            }

            return $businessLine;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le secteur d'activité ne soit pas supprimé
     *
     * @param BusinessLine $businessLine
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(BusinessLine $businessLine, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($businessLine->getDeleted() !== null && $businessLine->getDeleted() instanceof \DateTime && $businessLine->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('businessLineNotFound');
            }

            return true;

        }
        return false;
    }
}