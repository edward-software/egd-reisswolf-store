<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\PublicBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use \Exception;
use Paprec\CatalogBundle\Entity\Product;
use Paprec\PublicBundle\Entity\Cart;
use Symfony\Component\DependencyInjection\ContainerInterface;


class CartManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * Retourne un Cart en pasant son Id ou un object Cart
     * @param $cart
     * @return null|object|Cart
     * @throws Exception
     */
    public function get($cart)
    {
        $id = $cart;
        if ($cart instanceof Cart) {
            $id = $cart->getId();
        }
        try {

            $cart = $this->em->getRepository('PaprecPublicBundle:Cart')->find($id);

            if ($cart === null || $this->isDisabled($cart)) {
                throw new EntityNotFoundException('cartNotFound', 404);
            }

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'a ce jour, le cart ne soit pas désactivé
     *
     * @param Cart $cart
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDisabled(Cart $cart, $throwException = false)
    {
        $now = new \DateTime();

        if ($cart->getDisabled() !== null && $cart->getDisabled() instanceof \DateTime && $cart->getDisabled() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('cartNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Créé un nouveau Cart en initialisant sa date Disabled  dans Today + $deltaJours
     *
     * @param $deltaJours
     * @return Cart
     * @throws Exception
     */
    public function create($deltaJours)
    {
        try {

            $cart = new Cart();

            /**
             * Initialisant de $disabled
             */
            $now = new \DateTime();
            $disabledDate = $now->modify('+' . $deltaJours . 'day');
            $cart->setDisabled($disabledDate);


            $this->em->persist($cart);
            $this->em->flush();

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Ajoute du content au cart pour un produit et une quantité donnés
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return mixed
     * @throws Exception
     */
    public function addContent($id, Product $product, $quantity)
    {

        $cart = $this->get($id);

        $content = $cart->getContent();
        $newContent = ['pId' => $product->getId(), 'qtty' => $quantity];
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($value['pId'] == $product->getId()) {
                    unset($content[$key]);
                }
            }
        }

        $content[] = $newContent;
        $cart->setContent($content);
        $this->em->flush();
        return $cart;
    }


    /**
     * Supprime un produit
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContent($id, $productId)
    {
        $cart = $this->get($id);
        $products = $cart->getContent();
        if ($products && count($products)) {
            foreach ($products as $key => $product) {
                if ($product['pId'] == $productId) {
                    unset($products[$key]);
                }
            }
        }
        $cart->setContent($products);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }


}
