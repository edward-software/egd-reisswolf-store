<?php

namespace Paprec\CatalogBundle\Twig\Extension;


use Paprec\CatalogBundle\Entity\Product;
use Paprec\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProductNameTranslationExtension extends AbstractExtension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('productNameTranslation', array($this, 'productNameTranslation')),
        );
    }

    public function productNameTranslation($productId, User $user)
    {
        $productName = '';
        try {
            $productManager = $this->container->get('paprec_catalog.product_manager');
            $product = $productManager->get($productId);
            $productName = $productManager->getProductLabelByProductAndLocale($product, $user->getLang())->getName();
        } catch (\Exception $e) {
        }

        return $productName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatId';
    }
}
