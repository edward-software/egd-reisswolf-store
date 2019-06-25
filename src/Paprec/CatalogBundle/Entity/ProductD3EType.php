<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductD3EType
 *
 * @ORM\Table(name="productD3EType")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductD3ETypeRepository")
 */
class ProductD3EType
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
     * @var int
     *
     * @ORM\Column(name="unitPrice", type="integer")
     * @Assert\NotBlank()
     */
    private $unitPrice;


    /**************************************************************************************************
     * RELATIONS
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", inversedBy="productD3ETypes")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productD3E;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Type", inversedBy="productD3ETypes")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id", nullable=false)
     */
    private $type;

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
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductD3EType
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice.
     *
     * @return int
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return ProductD3EType
     */
    public function setProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E)
    {
        $this->productD3E = $productD3E;

        return $this;
    }

    /**
     * Get productD3E.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductD3E
     */
    public function getProductD3E()
    {
        return $this->productD3E;
    }

    /**
     * Set type.
     *
     * @param \Paprec\CatalogBundle\Entity\Type $type
     *
     * @return ProductD3EType
     */
    public function setType(\Paprec\CatalogBundle\Entity\Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \Paprec\CatalogBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }
}
