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
use Paprec\CatalogBundle\Entity\PostalCode;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PostalCodeManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($postalCode)
    {
        $id = $postalCode;
        if ($postalCode instanceof PostalCode) {
            $id = $postalCode->getId();
        }
        try {

            $postalCode = $this->em->getRepository('PaprecCatalogBundle:PostalCode')->find($id);

            if ($postalCode === null || $this->isDeleted($postalCode)) {
                throw new EntityNotFoundException('postalCodeNotFound');
            }

            return $postalCode;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le postalCode  n'est pas supprimé
     *
     * @param PostalCode $postalCode
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(PostalCode $postalCode, $throwException = false)
    {
        $now = new \DateTime();

        if ($postalCode->getDeleted() !== null && $postalCode->getDeleted() instanceof \DateTime && $postalCode->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('postalCodeNotFound');
            }
            return true;
        }
        return false;
    }


    /**
     * Renvoie le rate du postalCode correspondant à la division et au code postal passé en param
     * Return 100 (car divisé par 100 après) si pas de code postal trouvé
     * @param $postalCode
     * @param $division
     * @return float|int
     */
    public function getRateByPostalCodeDivision($postalCode, $division)
    {
        $rate = 100;
        $postalCodeDivs = $this->em->getRepository('PaprecCatalogBundle:PostalCode')->findBy(array(
            'division' => $division,
            'deleted' => null
        ));

        // On parcourt tous les codes postaux appartenant à la division
        if ($postalCodeDivs !== null && count($postalCodeDivs)) {
            $codesList = array();
            foreach ($postalCodeDivs as $pC) {
                if ($pC->getCodes() !== null) {
                    $codesList = explode(',', str_replace(' ', '', $pC->getCodes()));
                }
                if ($codesList !== null && count($codesList)) {
                    foreach ($codesList as $c) {
                        // si il existe un code postal exactement égal au $postalCode en param, alors on récupère son rate et on sort del a boucle
                        // ex : (92* == 92*) (92150 == 92150)
                        if ($c == $postalCode) {
                            $rate = $pC->getRate();
                            break;
                        }
                        // Sinon on regarde les deux premiers caractères
                        // ex : Il existe un code postal (code = 92*, ratio = 1.5),
                        //      On passe en param postalCode = 92150
                        //      Alors on test 92 == 92
                        else if (substr($c, 0, 2) == substr($postalCode, 0, 2)) {
                            $rate = $pC->getRate();
                            break;
                        }
                    }
                }
            }
        }
        return $rate;
    }
}
