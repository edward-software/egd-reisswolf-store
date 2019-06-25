<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3EORderLine
 *
 * @ORM\Table(name="productD3EOrderLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductD3EOrderLineRepository")
 */
class ProductD3EOrderLine
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;


    /**
     * @var string
     *
     * @ORM\Column(name="productName", type="string", length=255)
     */
    private $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="productSubName", type="string", length=255, nullable=true)
     */
    private $productSubName;

    /**
     * @var int
     *
     * @ORM\Column(name="unitPrice", type="integer")
     */
    private $unitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="totalAmount", type="integer")
     */
    private $totalAmount;


    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="La quantité doit être un nombre entier"
     * )
     */
    private $quantity;


    /**************************************************************************************************
     * RELATIONS
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", inversedBy="productD3EOrderLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productD3E;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EOrder", inversedBy="productD3EOrderLines")
     * @ORM\JoinColumn(name="productD3EOrderId", referencedColumnName="id", nullable=false)
     */
    private $productD3EOrder;


    /**
     * ProductD3EOrderLine constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }
    

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
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return ProductD3EOrderLine
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return ProductD3EOrderLine
     */
    public function setDateUpdate($dateUpdate = null)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime|null
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return ProductD3EOrderLine
     */
    public function setDeleted($deleted = null)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return \DateTime|null
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set productName.
     *
     * @param string $productName
     *
     * @return ProductD3EOrderLine
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set productSubName.
     *
     * @param string $productSubName
     *
     * @return ProductD3EOrderLine
     */
    public function setProductSubName($productSubName)
    {
        $this->productSubName = $productSubName;

        return $this;
    }

    /**
     * Get productSubName.
     *
     * @return string
     */
    public function getProductSubName()
    {
        return $this->productSubName;
    }

    /**
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductD3EOrderLine
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
     * Set totalAmount.
     *
     * @param int $totalAmount
     *
     * @return ProductD3EOrderLine
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount.
     *
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return ProductD3EOrderLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return ProductD3EOrderLine
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
     * Set productD3EOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder
     *
     * @return ProductD3EOrderLine
     */
    public function setProductD3EOrder(\Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder)
    {
        $this->productD3EOrder = $productD3EOrder;

        return $this;
    }

    /**
     * Get productD3EOrder.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductD3EOrder
     */
    public function getProductD3EOrder()
    {
        return $this->productD3EOrder;
    }
}
