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
     * Créé un nouveau Cart à partir d'un autre en copiant ses données géographiques
     * Utile lorsque l'on change de choix de division mais que l'on veut conserver les données "Je me situe"
     * @param $cart
     * @return Cart
     * @throws Exception
     */
    public function cloneCart($cart)
    {
        try {
            $cart = $this->get($cart);

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        $newCart = $this->create(90);
        $newCart->setLocation($cart->getLocation());
        $newCart->setPostalCode($cart->getPostalCode());
        $newCart->setCity($cart->getCity());
        $newCart->setLongitude($cart->getLongitude());
        $newCart->setLatitude($cart->getLatitude());

        $this->em->persist($newCart);

        return $newCart;
    }

    /**
     * Ajoute une displayedCategory au simple_array displayedCategories du cart si elle n'est pas déjà existante
     * La supprime si elle existe déjà
     * @param $id
     * @param $categoryId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedCategory($id, $categoryId)
    {
        $cart = $this->get($id);
        $dCategories = $cart->getDisplayedCategories();

        if (in_array($categoryId, $dCategories)) {
            $index = array_search($categoryId, $dCategories);
            array_splice($dCategories, $index, 1);
        } else {
            $dCategories[] = $categoryId;
        }
        $cart->setDisplayedCategories($dCategories);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute un displayedProduct à l'array dispplayedProducts du cart si il n'est pas déjà existant
     * avec comme clé, l'id de la catégorie
     * La supprime si elle existe déjà
     * @param $id
     * @param $categoryId
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedProduct($id, $categoryId, $productId)
    {
        $cart = $this->get($id);
        $dProducts = $cart->getDisplayedProducts();

        if ($dProducts && in_array($productId, $dProducts) && array_key_exists($categoryId, $dProducts)) {
            unset($dProducts[$categoryId]);
        } else {
            $dProducts = array();
            $dProducts[$categoryId] = $productId;
        }


        $cart->setDisplayedProducts($dProducts);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Pour D3E ou package,il n'y a pas de notion de catégorie, on remplace donc le displayedProdu
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedProductNoCat($id, $productId)
    {
        $cart = $this->get($id);
        $dProducts = $cart->getDisplayedProducts();

        if ($dProducts && in_array($productId, $dProducts)) {
            $dProducts = array();
        } else {
            $dProducts = array();
            $dProducts[] = $productId;
        }

        $cart->setDisplayedProducts($dProducts);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute du content au cart pour un produit sur mesure DI ou CHANTIER
     *
     * @param $id
     * @param $categoryId
     * @param $productId
     * @param $quantity
     * @return mixed
     * @throws Exception
     */
    public function addContent($id, $categoryId, $productId, $quantity)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();
        $product = ['cId' => $categoryId, 'pId' => $productId, 'qtty' => $quantity];
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($value['cId'] == $categoryId && $value['pId'] == $productId) {
                    unset($content[$key]);
                }
            }
        }

        $content[] = $product;
        $cart->setDisplayedProducts = array();
        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute du content au cart pour un produit packagé DI ou CHANTIER
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return object|Cart|null
     * @throws Exception
     */
    public function addContentPackage($id, $productId, $quantity)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();
        $product = ['pId' => $productId, 'qtty' => $quantity];
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($value['pId'] == $productId) {
                    unset($content[$key]);
                }
            }
        }

        $content[] = $product;
        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute du content au cart pour un produit packagé D3E ou CHANTIER
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return object|Cart|null
     * @throws Exception
     */
    public function addOneProductPackage($id, $productId)
    {
        $cart = $this->get($id);
        $qtty = '1';
        $content = $cart->getContent();
        if ($content && count($content)) {
            foreach ($content as $key => $product) {
                if ($product['pId'] == $productId) {
                    $qtty = strval(intval($product['qtty']) + 1);
                    unset($content[$key]);
                }
            }
        }
        $product = ['pId' => $productId, 'qtty' => $qtty];
        $content[] = $product;

        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Elève 1 de de quantité au cart pour un produit packagé CHANTIER OU D3E
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return object|Cart|null
     * @throws Exception
     */
    public function removeOneProductPackage($id, $productId)
    {
        $cart = $this->get($id);
        $qtty = '0';
        $content = $cart->getContent();
        if ($content && count($content)) {
            foreach ($content as $key => $product) {
                if ($product['pId'] == $productId) {
                    $qtty = strval(intval($product['qtty']) - 1);
                    unset($content[$key]);
                }
            }
        }

        if ($qtty !== '0') {
            $product = ['pId' => $productId, 'qtty' => $qtty];
            $content[] = $product;
        }

        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute du content au cart pour un produit packagé D3E
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @param $option
     * @return null|object|Cart
     * @throws Exception
     */
    public function addContentD3EPackage($id, $productId, $quantity, $optHandling, $optSerialNumberStmt, $optDestruction)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();
        $product = ['pId' => $productId, 'qtty' => $quantity, 'optHandling' => $optHandling, 'optSerialNumberStmt' => $optSerialNumberStmt, 'optDestruction' => $optDestruction];
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($value['pId'] == $productId) {
                    unset($content[$key]);
                }
            }
        }
        $content[] = $product;
        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute au cart le contenu d'un produit sur mesure D3E avec ses types sélectionnés, options et quantité
     *
     * @param $id
     * @param $productD3EType
     * @return object|Cart|null
     * @throws Exception
     */
    public function addContentD3E($id, $productD3EType)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();

        $productId = $productD3EType['productId'];
        $productType = [
            'tId' => $productD3EType['typeId'],
            'qtty' => $productD3EType['qtty'],
            'optHandling' => $productD3EType['optHandling'],
            'optSerialNumberStmt' => $productD3EType['optSerialNumberStmt'],
            'optDestruction' => $productD3EType['optDestruction']
        ];
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($key == $productId) {
                    foreach ($value as $key2 => $contentType) {
                        if ($contentType['tId'] == $productType['tId']) {
                            unset($content[$key][$key2]);
                        }
                    }
                }
            }
        }
        if ($productType['qtty'] > 0) {
            $content[$productId][] = $productType;
        }

        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;

    }

    /**
     * Supprime un produit
     * @param $id
     * @param $categoryId
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContent($id, $categoryId, $productId)
    {
        $cart = $this->get($id);
        $products = $cart->getContent();
        if ($products && count($products)) {
            foreach ($products as $key => $product) {
                if ($product['cId'] == $categoryId && $product['pId'] == $productId) {
                    unset($products[$key]);
                }
            }
        }
        $cart->setContent($products);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Supprime un produit packagé
     * @param $id
     * @param $categoryId
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContentPackage($id, $productId)
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

    /**
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContentD3E($id, $productId)
    {
        $cart = $this->get($id);
        $products = $cart->getContent();
        if ($products && count($products)) {
            foreach ($products as $key => $product) {
                if ($key == $productId) {
                    unset($products[$key]);
                }
            }
        }
        $cart->setContent($products);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits dans le Cart dans la partie "Mon besoin"
     * ainsi que la somme du prix du Cart
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartDI($id)
    {
        $cart = $this->get($id);
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        // on récupère les products ajoutés au cart
        $productsCategories = $cart->getContent();
        // On récupère le code postal
        $postalCode = $cart->getPostalCode();

        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($productsCategories && count($productsCategories)) {
            foreach ($productsCategories as $productsCategory) {
                $productDI = $productDIManager->get($productsCategory['pId']);
                $category = $categoryManager->get($productsCategory['cId']);
                $loadedCart[$productsCategory['pId'] . '_' . $productsCategory['cId']] = ['qtty' => $productsCategory['qtty'], 'pName' => $productDI->getName(), 'pCapacity' => $productDI->getCapacity() . $productDI->getCapacityUnit(), 'cName' => $category->getName(), 'frequency' => $cart->getFrequency()];
            }
            $loadedCart['sum'] = $this->calculateSumDI($productsCategories, $postalCode);
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }

    /**
     * Renvoie la somme totale des produits DI du Cart
     * Récupère le prix unitaire, défini dans ProductDICategory, que l'on multiplie par la quantité
     * mais aussi par le ratio en fonction du code postal
     *
     * @param $productsCategories
     * @param $postalCode
     * @param $productDIManager
     * @param $categoryManager
     * @return float|int
     * @throws Exception
     */
    private function calculateSumDI($productsCategories, $postalCode)
    {
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');


        $sum = 0;
        foreach ($productsCategories as $productsCategory) {
            $productDI = $productDIManager->get($productsCategory['pId']);
            $category = $categoryManager->get($productsCategory['cId']);
            $productDICategory = $this->em->getRepository('PaprecCatalogBundle:ProductDICategory')->findOneBy(
                array(
                    'productDI' => $productDI,
                    'category' => $category
                )
            );
            if ($productDICategory !== null) {
                $sum += $productDIManager->calculatePrice($postalCode, $productDICategory->getUnitPrice(), $productsCategory['qtty']);
            }
        }
        return $numberManager->normalize($sum);
    }

    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits dans le Cart dans la partie "Mon besoin"
     * ainsi que la somme du prix du Cart
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartChantier($id)
    {
        $cart = $this->get($id);
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        // on récupère les products ajoutés au cart
        $productsCategories = $cart->getContent();
        // On récupère le code postal
        $postalCode = $cart->getPostalCode();

        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($productsCategories && count($productsCategories)) {

            foreach ($productsCategories as $productsCategory) {
                $productChantier = $productChantierManager->get($productsCategory['pId']);
                $category = $categoryManager->get($productsCategory['cId']);
                $loadedCart[$productsCategory['pId'] . '_' . $productsCategory['cId']] = ['qtty' => $productsCategory['qtty'], 'pName' => $productChantier->getName(), 'pCapacity' => $productChantier->getCapacity() . $productChantier->getCapacityUnit(), 'cName' => $category->getName(), 'frequency' => $cart->getFrequency()];
            }
            $loadedCart['sum'] = $this->calculateSumChantier($productsCategories, $postalCode);
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }


    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits packagés dans le Cart dans la partie "Mon besoin"
     *
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartPackageChantier($id)
    {
        $cart = $this->get($id);
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');


        //On récupère les produits du Cart
        $products = $cart->getContent();
        // on récupère le code postal
        $postalCode = $cart->getPostalCode();

        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($products && count($products)) {
            foreach ($products as $product) {
                $productChantier = $productChantierManager->get($product['pId']);
                $loadedCart[$product['pId']] = ['qtty' => $product['qtty'], 'pName' => $productChantier->getName(), 'pSubName' => $productChantier->getSubName()];
            }
            $loadedCart['sum'] = $this->calculateSumChantierPackage($products, $postalCode);
        }

        return $loadedCart;
    }

    /**
     * Renvoie la somme totale des produits Chantier du Cart
     * Récupère le prix unitaire, défini dans ProductDICategory, que l'on multiplie par la quantité
     * mais aussi par le ratio en fonction du code postal
     *
     * @param $productsCategories
     * @param $postalCode
     * @param $productDIManager
     * @param $categoryManager
     * @return float|int
     * @throws Exception
     */
    private function calculateSumChantier($productsCategories, $postalCode)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $sum = 0;
        foreach ($productsCategories as $productsCategory) {
            $productChantier = $productChantierManager->get($productsCategory['pId']);
            $category = $categoryManager->get($productsCategory['cId']);
            $productChantierCategory = $this->em->getRepository('PaprecCatalogBundle:ProductChantierCategory')->findOneBy(
                array(
                    'productChantier' => $productChantier,
                    'category' => $category
                )
            );
            if ($productChantierCategory !== null) {
                $sum += $productChantierManager->calculatePrice($postalCode, $productChantierCategory->getUnitPrice(), $productsCategory['qtty']);
            }
        }

        // on normalize la somme pour l'afficher avec le formatAmount twig
        return $numberManager->normalize($sum);
    }

    /**
     * Renvoie la somme totale des produits Chantier packagés du Cart
     *
     * @param $products
     * @param $postalCode
     * @return float|null
     * @throws Exception
     */
    public function calculateSumChantierPackage($products, $postalCode)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $sum = 0;
        foreach ($products as $product) {
            $productChantier = $productChantierManager->get($product['pId']);
            $sum += $productChantierManager->calculatePrice($postalCode, $productChantier->getPackageUnitPrice(), $product['qtty']);
        }
        return $numberManager->normalize($sum);
    }


    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits dans le Cart dans la partie "Mon besoin"
     * ainsi que la somme du prix du Cart
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartD3E($id)
    {
        $cart = $this->get($id);
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');
        $priceListD3EManager = $this->container->get('paprec_catalog.price_list_d3e_manager');
        $typeManager = $this->container->get('paprec_catalog.type_manager');


        // on récupère les products ajoutés au Cart
        $products = $cart->getContent();
        // on récupère le postalCode du Cart
        $postalCode = $cart->getPostalCode();

        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($products && count($products)) {
            foreach ($products as $id => $product) {
                $productD3E = $productD3EManager->get($id);
                foreach ($product as $productType) {
                    $type = $typeManager->get($productType['tId']);
                    $nbOptions = $productType['optHandling'] + $productType['optSerialNumberStmt'] + $productType['optDestruction'];
                    $loadedCart[$id]['pName'] = $productD3E->getName();
                    $loadedCart[$id]['types'][] = ['qtty' => $productType['qtty'], 'type' => $type->getName(), 'nbOptions' => $nbOptions];
                }
//                $loadedCart['sum'] += $priceListD3EManager->getUnitPriceByPostalCodeQtty($productD3E->getPriceListD3E(), $postalCode, $product['qtty']) * $product['qtty'];
            }
//            $loadedCart['sum'] = $this->calculateSumD3E($products, $postalCode);
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
//        ksort($loadedCart);

        return $loadedCart;
    }

    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits packagés dans le Cart dans la partie "Mon besoin"
     *
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartPackageD3E($id)
    {
        $cart = $this->get($id);
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');


        //On récupère les produits du Cart
        $products = $cart->getContent();
        // on récupère le code postal
        $postalCode = $cart->getPostalCode();

        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($products && count($products)) {
            foreach ($products as $product) {
                $productD3E = $productD3EManager->get($product['pId']);
                $loadedCart[$product['pId']] = ['qtty' => $product['qtty'], 'pName' => $productD3E->getName(), 'pSubName' => $productD3E->getSubName()];
            }
            $loadedCart['sum'] = $this->calculateSumD3EPackage($products, $postalCode);
        }

        return $loadedCart;
    }

    private function calculateSumD3E($products, $postalCode)
    {
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $priceListD3EManager = $this->container->get('paprec_catalog.price_list_d3e_manager');
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');

        $sum = 0;
        foreach ($products as $product) {
            $productD3E = $productD3EManager->get($product['pId']);
            $unitPrice = $priceListD3EManager->getUnitPriceByPostalCodeQtty($productD3E->getPriceListD3E(), $postalCode, $product['qtty']);

            $sum += $productD3EManager->calculatePrice($productD3E, $postalCode, $unitPrice, $product['qtty'], $product['optHandling'], $product['optSerialNumberStmt'], $product['optDestruction']);
        }
        return $numberManager->normalize($sum);
    }

    /**
     * Renvoie la somme totale des produits D3E packagés du Cart
     *
     * @param $products
     * @param $postalCode
     * @return float|null
     * @throws Exception
     */
    public function calculateSumD3EPackage($products, $postalCode)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_d3e_manager');
        $numberManager = $this->container->get('paprec_catalog.number_manager');

        $sum = 0;
        foreach ($products as $product) {
            $productD3E = $productD3EManager->get($product['pId']);
            $sum += $productD3EManager->calculatePricePackage($postalCode, $productD3E->getPackageUnitPrice(), $product['qtty']);
        }
        return $numberManager->normalize($sum);
    }
}
