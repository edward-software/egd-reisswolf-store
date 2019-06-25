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
use Paprec\CatalogBundle\Entity\CustomizableArea;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomizableAreaManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($customizableArea)
    {
        $id = $customizableArea;
        if ($customizableArea instanceof CustomizableArea) {
            $id = $customizableArea->getId();
        }
        try {

            $customizableArea = $this->em->getRepository('PaprecCatalogBundle:CustomizableArea')->find($id);

            if ($customizableArea === null || $this->isDeleted($customizableArea)) {
                throw new EntityNotFoundException('customizableAreaNotFound');
            }

            return $customizableArea;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la zone personnalisable n'est pas supprimée
     *
     * @param CustomizableArea $customizableArea
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(CustomizableArea $customizableArea, $throwException = false)
    {
        $now = new \DateTime();

        if ($customizableArea->getDeleted() !== null && $customizableArea->getDeleted() instanceof \DateTime && $customizableArea->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('customizableAreaNotFound');
            }

            return true;

        }
        return false;
    }


    /**
     * @param $code
     * @return object|CustomizableArea|null
     * @throws Exception
     */
    public function getByCode($code)
    {
        try {

            $customizableArea = $this->em->getRepository('PaprecCatalogBundle:CustomizableArea')->findOneBy(array(
                'code' => $code,
                'isDisplayed' => true,
                'deleted' => null
            ));

            if ($customizableArea === null) {
                return null;
            }

            return $customizableArea;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Renvoie les codes définis dans les parameters qui ne sont pas déjà associés à un CustomizableAera existant
     * @return array
     */
    public function getUnallocated()
    {
        $unallocatedCodes = array();
        $allocatedCodes = $this->em->getRepository('PaprecCatalogBundle:CustomizableArea')->findAll();

        foreach ($this->container->getParameter('paprec_customizable_area_codes') as $c) {
            $allocated = false;
            if ($allocatedCodes != null && is_array($allocatedCodes) && count($allocatedCodes)) {
                foreach ($allocatedCodes as $code) {
                    if ($code->getCode() == $c) {
                        $allocated = true;
                    }
                }
            }
            if (!$allocated) {
                $unallocatedCodes[$c] = $c;
            }
        }
        return $unallocatedCodes;
    }

}