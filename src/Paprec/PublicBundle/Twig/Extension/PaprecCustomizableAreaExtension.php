<?php

namespace Paprec\PublicBundle\Twig\Extension;


use Exception;
use Symfony\Component\DependencyInjection\Container;

class PaprecCustomizableAreaExtension extends \Twig_Extension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('paprec_customizable_area', array($this, 'customizableArea')),
        );
    }

    /**
     * @param $code
     * @return array|object[]|\Paprec\CatalogBundle\Entity\CustomizableArea[]
     * @throws Exception
     */
    public function customizableArea($code)
    {
        try {
            $customizableAreaManager = $this->container->get('paprec_catalog.customizable_area_manager');

            return $customizableAreaManager->getByCode($code);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'paprec_customizable_area';
    }
}
