<?php

namespace Paprec\CatalogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PostalCodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostalCodeRepository extends EntityRepository
{
    public function findLikeByCode($query)
    {
        $queryBuilder = $this
            ->createQueryBuilder('p')
            ->where('p.code LIKE :code')
            ->andWhere('p.deleted IS NULL')
            ->setParameter('code', $query . '%')
            ->orderBy('p.code', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Vérifie s'il existe ddéjà un postalCode NON supprimé aec le même code
     *
     * @param $params
     * @return array
     */
    public function isCodeUnique($params)
    {
        return $this->findBy(array(
            'code' => $params['code'],
            'deleted' => null
        ));
    }
}
