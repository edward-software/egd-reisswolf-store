<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 30/11/2018
 * Time: 16:42
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Paprec\CatalogBundle\Entity\Region;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegionManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($region)
    {
        $id = $region;
        if ($region instanceof Region) {
            $id = $region->getId();
        }
        try {

            $region = $this->em->getRepository('PaprecCatalogBundle:Region')->find($id);

            if ($region === null || $this->isDeleted($region)) {
                throw new EntityNotFoundException('regionNotFound');
            }

            return $region;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le region  n'est pas supprimée
     *
     * @param Region $region
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(Region $region, $throwException = false)
    {
        $now = new \DateTime();

        if ($region->getDeleted() !== null && $region->getDeleted() instanceof \DateTime && $region->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('regionNotFound');
            }
            return true;
        }
        return false;
    }
}
