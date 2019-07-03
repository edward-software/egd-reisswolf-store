<?php

namespace Paprec\CatalogBundle\Twig\Extension;


use Paprec\CatalogBundle\Entity\Product;
use Symfony\Component\DependencyInjection\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProductLabelTranslationExtension extends AbstractExtension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('productLabelTranslation', array($this, 'productLabelTranslation')),
        );
    }

    public function productLabelTranslation(Product $product, $lang, $attr = null)
    {
        $returnLabel = '';
        try {
            $productManager = $this->container->get('paprec_catalog.product_manager');
            $product = $productManager->get($product);
            switch ($attr) {
                case 'shortDescription':
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getShortDescription();
                    break;
                default:
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getName();
            }
        } catch (\Exception $e) {
        }

        return $returnLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatId';
    }
}
