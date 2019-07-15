<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\UserBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Paprec\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * Retourne un User en passant son Id ou un object USer
     * @param $user
     * @return null|object|User
     * @throws Exception
     */
    public function get($user)
    {
        $id = $user;
        if ($user instanceof User) {
            $id = $user->getId();
        }
        try {
            $user = $this->em->getRepository('PaprecUserBundle:User')->find($id);

            /**
             * Vérification que le user existe ou ne soit pas supprimé
             */
            if ($user === null || $this->isDeleted($user)) {
                throw new EntityNotFoundException('userNotFound');
            }


            return $user;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le user ce soit pas supprimé
     * @param User $user
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(User $user, $throwException = false)
    {
        $now = new \DateTime();

        if ($user->getDeleted() !== null && $user->getDeleted() instanceof \DateTime && $user->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('userNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * @param $postalCode
     * @return object|User|null
     * @throws Exception
     */
    public function getUserInChargeByPostalCode($postalCode)
    {
        try {
            if ($postalCode == null) {
                return null;
            }

            $postalCode = $this->em->getRepository('PaprecCatalogBundle:PostalCode')->findOneBy(array(
                'code' => $postalCode
            ));

            $user = null;
            if ($postalCode != null) {

                $user = $postalCode->getUserInCharge();
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }
}
