<?php

namespace Goondi\ToolsBundle\Twig\Extension;

use Goondi\ToolsBundle\Services\CurrencyManager;

class CurrencyExtension extends \Twig_Extension
{

    private $currencyManager;

    public function __construct(CurrencyManager $currencyManager)
    {
        $this->currencyManager = $currencyManager;
    }


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('currencyName', array($this, 'currencyName')),
        );
    }

    public function currencyName($code)
    {
        return $this->currencyManager->getName($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'currencyName';
    }
}
