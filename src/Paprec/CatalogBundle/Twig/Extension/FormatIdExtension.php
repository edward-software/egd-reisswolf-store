<?php

namespace Paprec\CatalogBundle\Twig\Extension;


use Symfony\Component\DependencyInjection\Container;

class FormatIdExtension extends \Twig_Extension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatId', array($this, 'formatId')),
        );
    }

    public function formatId($id, $padlength, $padstring = 0, $pad_type = STR_PAD_LEFT)
    {
        return str_pad($id, $padlength, $padstring, $pad_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatId';
    }
}
