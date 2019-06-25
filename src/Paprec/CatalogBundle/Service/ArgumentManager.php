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
use Paprec\CatalogBundle\Entity\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArgumentManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($argument)
    {
        $id = $argument;
        if ($argument instanceof Argument) {
            $id = $argument->getId();
        }
        try {

            $argument = $this->em->getRepository('PaprecCatalogBundle:Argument')->find($id);

            if ($argument === null || $this->isDeleted($argument)) {
                throw new EntityNotFoundException('argumentNotFound');
            }

            return $argument;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour l'argument  n'est pas supprimé
     *
     * @param Argument $argument
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(Argument $argument, $throwException = false)
    {
        $now = new \DateTime();

        if ($argument->getDeleted() !== null && $argument->getDeleted() instanceof \DateTime && $argument->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('argumentNotFound');
            }

            return true;

        }
        return false;
    }

}