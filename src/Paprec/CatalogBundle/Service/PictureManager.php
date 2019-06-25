<?php
/**
 * Created by PhpStorm.
 * User: agb
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\PictureLabel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PictureManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($picture)
    {
        $id = $picture;
        if ($picture instanceof Picture) {
            $id = $picture->getId();
        }
        try {

            $picture = $this->em->getRepository('PaprecCatalogBundle:Picture')->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($picture === null) {
                throw new EntityNotFoundException('pictureNotFound');
            }


            return $picture;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


}
