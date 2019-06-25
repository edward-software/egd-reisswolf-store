<?php

namespace Goondi\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Debtor
 *
 * @ORM\Table(name="ggs_currencyRates")
 * @ORM\Entity()
 */
class CurrencyRate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="source", type="integer", nullable=false)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="integer", nullable=false)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $rate;

	
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set source
     *
     * @param integer $source
     * @return CurrencyRate
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return integer 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set target
     *
     * @param integer $target
     * @return CurrencyRate
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return integer 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set rate
     *
     * @param string $rate
     * @return CurrencyRate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string 
     */
    public function getRate()
    {
        return $this->rate;
    }
}
