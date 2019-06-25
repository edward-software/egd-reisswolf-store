<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CatalogBundle\Entity\PriceListD3E;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PriceListD3EManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($priceListD3E)
    {
        $id = $priceListD3E;
        if ($priceListD3E instanceof PriceListD3E) {
            $id = $priceListD3E->getId();
        }
        try {

            $priceListD3E = $this->em->getRepository('PaprecCatalogBundle:PriceListD3E')->find($id);

            if ($priceListD3E === null || $this->isDeleted($priceListD3E)) {
                throw new EntityNotFoundException('priceListD3ENotFound');
            }

            return $priceListD3E;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'aà ce jours la grille tarifaire
     *
     * @param PriceListD3E $priceListD3E
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(PriceListD3E $priceListD3E, $throwException = false)
    {
        $now = new \DateTime();

        if ($priceListD3E->getDeleted() !== null && $priceListD3E->getDeleted() instanceof \DateTime && $priceListD3E->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('priceListD3ENotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Vérifie si il existe des ProductD3E qui sont liés au priceListD3E
     * Retourne vrai s'il y en a, faux sinon
     *
     * @param $priceListD3EId
     * @return bool
     * @throws Exception
     */
    public function hasRelatedProductD3E($priceListD3EId)
    {
        try {
            $query = $this->em
                ->getRepository(PriceListD3E::class)
                ->createQueryBuilder('pl')
                ->select('COUNT(pl)')
                ->innerJoin('PaprecCatalogBundle:ProductD3E', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'pl.id = p.priceListD3E')
                ->where('pl.id = :priceListId')
                ->andWhere('p.deleted is null')
                ->setParameter('priceListId', $priceListD3EId);
            return ($query->getQuery()->getSingleScalarResult() > 0);
        } catch (ORMException $e) {
            throw new Exception($e->getMessage(), 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    /**
     * Fonction qui récupère le prix la priceListLineD3E qui correspond à la grille, au code postal et à la quantité en param
     *
     * @param PriceListD3E $priceListD3E
     * @param $postalCodeQuote
     * @param $qtty
     * @return int
     */
    public function getUnitPriceByPostalCodeQtty(PriceListD3E $priceListD3E, $postalCodeQuote, $qtty)
    {
        $lignesPostalCodeMatch = array();
        $return = 0;

        // On parcourt toutes les lignes de la grille pour récupérer celles qui possèdent le postalCodeQuote
        foreach ($priceListD3E->getPriceListLineD3Es() as $priceListLineD3E) {
            $postalCodes = str_replace(' ', '', $priceListLineD3E->getPostalCodes());
            $postalCodesArray = explode(',', $postalCodes);

            foreach ($postalCodesArray as $pC) {
                //on teste juste les deux premiers caractères pour avoir le code du département
                if (substr($pC, 0, 2) == substr($postalCodeQuote, 0, 2)) {
                    $lignesPostalCodeMatch[] = $priceListLineD3E;
                }
            }
        }
        // On récupère ensuite la ligne dont les tranches Min et Max comprennt la $qtty
        // Attention, pas de contrôle si plusieurs lignes se chevauchent, à l'utilisateur de gérer
        foreach ($lignesPostalCodeMatch as $ligne) {
            if ($qtty >= $ligne->getMinQuantity() && $qtty <= $ligne->getMaxQuantity()) {
                $return = $ligne->getPrice();
            }
        }

        return $return;
    }
}