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
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CatalogBundle\Entity\ProductD3E;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3E)
    {
        $id = $productD3E;
        if ($productD3E instanceof ProductD3E) {
            $id = $productD3E->getId();
        }
        try {

            $productD3E = $this->em->getRepository('PaprecCatalogBundle:ProductD3E')->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($productD3E === null || $this->isDeleted($productD3E)) {
                throw new EntityNotFoundException('productD3ENotFound');
            }

            return $productD3E;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le produit n'est pas supprimé
     *
     * @param ProductD3E $productD3E
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductD3E $productD3E, $throwException = false)
    {
        $now = new \DateTime();

        if ($productD3E->getDeleted() !== null && $productD3E->getDeleted() instanceof \DateTime && $productD3E->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productD3ENotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * On passe en paramètre les options Type et PostalCode, retourne les produits  qui sont disponibles dans le postalCode
     * et si le Type est 'Order' alors il faut vérifier que les produits retournés sont payables en ligne
     * @param $categoryId
     * @param $type
     * @return mixed
     * @throws Exception
     */
    public function findAvailables($options)
    {
        $postalCode = $options['postalCode'];
        $isPackage = $options['isPackage'];

        try {
            $query = $this->em
                ->getRepository(ProductD3E::class)
                ->createQueryBuilder('p')
                ->where('p.deleted is NULL')
                ->andWhere('p.isPackage = :package')
                ->orderBy('p.position', 'ASC')
                ->setParameter('package', $isPackage);

            $products = $query->getQuery()->getResult();

            $productsPostalCodeMatch = array();

            // On parcourt tous les produits D3E pour récupérer ceux  qui possèdent le postalCode
            foreach ($products as $product) {
                $postalCodes = str_replace(' ', '', $product->getAvailablePostalCodes());
                $postalCodesArray = explode(',', $postalCodes);
                foreach ($postalCodesArray as $pC) {

                    //on teste juste les deux premiers caractères pour avoir le code du département
                    if (substr($pC, 0, 2) == substr($postalCode, 0, 2)) {
                        $productsPostalCodeMatch[] = $product;
                    }
                }
            }


            return $productsPostalCodeMatch;

        } catch (ORMException $e) {
            throw new Exception('unableToGetProductD3Es', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * On passe en paramètre le PostalCode
     * retourne les produits packagés qui sont disponibles dans le postalCode, displayed et non supprimés
     * @param $postalCode
     * @return array
     * @throws Exception
     */
    public function findPackagesAvailable($postalCode) {
        try {
            $query = $this->em
                ->getRepository(ProductD3E::class)
                ->createQueryBuilder('p')
                ->where('p.isPackage = true')
                ->andWhere('p.deleted is null')
                ->andWhere('p.isDisplayed = true');

            $products = $query->getQuery()->getResult();

            $productsPostalCodeMatch = array();


            // On parcourt tous les produits D3E pour récupérer ceux  qui possèdent le postalCode
            foreach ($products as $product) {
                $postalCodes = str_replace(' ', '', $product->getAvailablePostalCodes());
                $postalCodesArray = explode(',', $postalCodes);
                foreach ($postalCodesArray as $pC) {
                    //on teste juste les deux premiers caractères pour avoir le code du département
                    if (substr($pC, 0, 2) == substr($postalCode, 0, 2)) {
                        $productsPostalCodeMatch[] = $product;
                    }
                }
            }
            return $productsPostalCodeMatch;

        } catch (ORMException $e) {
            throw new Exception('unableToGetProductD3Es', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Fonction calculant le prix d'un produit en fonction de sa quantité, du code postal et des options sélectionnées
     * Utilisée dans le calcul du montant d'un Cart et dans le calcul du montant d'une ligne ProductD3EQuoteLine
     * Si le calcul est modifiée, il faudra donc le modifier uniquement ici
     *
     * @param $postalCode
     * @param $unitPrice
     * @param $qtty
     * @return float|int
     */
    public function calculatePrice($productD3E, $postalCode, $unitPrice, $qtty, $optHandling, $optSerialNumberStmt, $optDestruction)
    {
        $postalCodeManager = $this->container->get('paprec_catalog.postal_code_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $productD3E = $this->get($productD3E);

        // Pour chaque option, si elle est sélectionné, on récupère son coefficient dénormalisé. Sinon on returne 1
        $rateHandling = ($optHandling == 1) ? $numberManager->denormalize($productD3E->getCoefHandling()) : 1;
        $rateSerialNumberStmt = ($optSerialNumberStmt == 1) ? $numberManager->denormalize($productD3E->getCoefSerialNumberStmt()) : 1;
        $rateDestruction = ($optDestruction == 1) ? $numberManager->denormalize($productD3E->getCoefDestruction()) : 1;

        $ratePostalCode = $postalCodeManager->getRateByPostalCodeDivision($postalCode, 'D3E');

        return $numberManager->denormalize($unitPrice) * $qtty * $numberManager->denormalize($ratePostalCode) * $rateHandling * $rateSerialNumberStmt * $rateDestruction;
    }


    /**
     * Fonction calculant le prix d'un produit packagé en fonction de sa quantité, du code postal
     * Si le calcul est modifiée, il faudra donc le modifier uniquement ici
     *
     * @param $postalCode
     * @param $unitPrice
     * @param $qtty
     * @return float|int
     */
    public function calculatePricePackage($postalCode, $unitPrice, $qtty) {
        $postalCodeManager = $this->container->get('paprec_catalog.postal_code_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');


        $ratePostalCode = $postalCodeManager->getRateByPostalCodeDivision($postalCode, 'D3E');

        // avant d'effectuer la multiplication, on dénormalise les valeurs qui sont normalisés en base
        return $numberManager->denormalize($unitPrice) * $qtty * $numberManager->denormalize($ratePostalCode);
    }

}
