<?php

namespace Paprec\CatalogBundle\Twig\Extension;


use Symfony\Component\DependencyInjection\Container;

class FormatAmountExtension extends \Twig_Extension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatAmount', array($this, 'formatAmount')),
        );
    }

    public function formatAmount($amount, $currency = null, $locale, $type = null)
    {
        if ($type == 'PERCENTAGE') {
            $currency = 'PERCENTAGE';
        }

        $formatManager = $this->container->get('paprec_catalog.number_manager');
        if ($type == 'FORMAT15') {
            return $formatManager->formatAmount15($amount, $locale);
        }

        if ($type === 'DEC2') {
            $amount = str_replace(',', '.', $amount);
            return  number_format((float)$amount, 2);
        }

        return $formatManager->formatAmount($amount, $currency, $locale);

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatAmount';
    }
}
