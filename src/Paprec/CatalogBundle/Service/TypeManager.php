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
use Paprec\CatalogBundle\Entity\Type;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TypeManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($type)
    {
        $id = $type;
        if ($type instanceof Type) {
            $id = $type->getId();
        }
        try {

            $type = $this->em->getRepository('PaprecCatalogBundle:Type')->find($id);

            if ($type === null || $this->isDeleted($type)) {
                throw new EntityNotFoundException('typeeNotFound');
            }

            return $type;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour l'productD3EType  n'est pas supprimé
     *
     * @param Type $type
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(Type $type, $throwException = false)
    {
        $now = new \DateTime();

        if ($type->getDeleted() !== null && $type->getDeleted() instanceof \DateTime && $type->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('typeNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * On passe en paramètre les options ProductD3E et PostalCode, retourne les types qui appartiennent au produit
     * @param $options
     * @return array
     * @throws Exception
     */
    public function findAvailables($options)
    {
        $productId = $options['product'];

        try {
            $query = $this->em
                ->getRepository(Type::class)
                ->createQueryBuilder('t')
                ->innerJoin('PaprecCatalogBundle:ProductD3EType', 'pt', \Doctrine\ORM\Query\Expr\Join::WITH, 't.id = pt.type')
                ->where('pt.productD3E = :product')
                ->orderBy('t.name', 'ASC')
                ->setParameter("product", $productId);

            $types = $query->getQuery()->getResult();

            return $types;

        } catch (ORMException $e) {
            throw new Exception('unableToGetTypes', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
