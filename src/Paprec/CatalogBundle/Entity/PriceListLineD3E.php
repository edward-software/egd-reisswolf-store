<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * PriceListLineD3E
 *
 * @ORM\Table(name="priceListLineD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PriceListLineD3ERepository")
 */
class PriceListLineD3E
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCodes", type="text")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^(\d{2}(\*|(?:\d{3}))(,\s*)?)+$/",
     *     match=true,
     *     message="Les codes postaux doivent être des nombres séparés par des virgules (ex: 75*, 92150, 36*)"
     * )
     */
    private $postalCodes;

    /**
     * @var int
     *
     * @ORM\Column(name="minQuantity", type="integer")
     * @Assert\NotBlank()
     */
    private $minQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="maxQuantity", type="integer")
     */
    private $maxQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\PriceListD3E", inversedBy="priceListLineD3Es")
     */
    private $priceListD3E;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="priceListLineD3Es")
     */
    private $agency;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set postalCodes.
     *
     * @param string $postalCodes
     *
     * @return PriceListLineD3E
     */
    public function setPostalCodes($postalCodes)
    {
        $this->postalCodes = $postalCodes;

        return $this;
    }

    /**
     * Get postalCodes.
     *
     * @return string
     */
    public function getPostalCodes()
    {
        return $this->postalCodes;
    }

    /**
     * Set minQuantity.
     *
     * @param int $minQuantity
     *
     * @return PriceListLineD3E
     */
    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;

        return $this;
    }

    /**
     * Get minQuantity.
     *
     * @return int
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * Set maxQuantity.
     *
     * @param int $maxQuantity
     *
     * @return PriceListLineD3E
     */
    public function setMaxQuantity($maxQuantity)
    {
        $this->maxQuantity = $maxQuantity;

        return $this;
    }

    /**
     * Get maxQuantity.
     *
     * @return int
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * Set priceListD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\PriceListD3E|null $priceListD3E
     *
     * @return PriceListLineD3E
     */
    public function setPriceListD3E(\Paprec\CatalogBundle\Entity\PriceListD3E $priceListD3E = null)
    {
        $this->priceListD3E = $priceListD3E;

        return $this;
    }

    /**
     * Get priceListD3E.
     *
     * @return \Paprec\CatalogBundle\Entity\PriceListD3E|null
     */
    public function getPriceListD3E()
    {
        return $this->priceListD3E;
    }

    /**
     * Set agency.
     *
     * @param \Paprec\CommercialBundle\Entity\Agency|null $agency
     *
     * @return PriceListLineD3E
     */
    public function setAgency(\Paprec\CommercialBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency.
     *
     * @return \Paprec\CommercialBundle\Entity\Agency|null
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set price.
     *
     * @param int $price
     *
     * @return PriceListLineD3E
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
}
