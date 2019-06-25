<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductChantierORderLine
 *
 * @ORM\Table(name="productChantierOrderLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductChantierOrderLineRepository")
 */
class ProductChantierOrderLine
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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductChantier", inversedBy="productChantierOrderLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productChantier;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierOrder", inversedBy="productChantierOrderLines")
     * @ORM\JoinColumn(name="productChantierOrderId", referencedColumnName="id", nullable=false)
     */
    private $productChantierOrder;


    /**
     * ProductChantierOrderLine constructor.
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * @return ProductChantierOrderLine
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
     * Set productChantier.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantier $productChantier
     *
     * @return ProductChantierOrderLine
     */
    public function setProductChantier(\Paprec\CatalogBundle\Entity\ProductChantier $productChantier)
    {
        $this->productChantier = $productChantier;

        return $this;
    }

    /**
     * Get productChantier.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductChantier
     */
    public function getProductChantier()
    {
        return $this->productChantier;
    }

    /**
     * Set productChantierOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder
     *
     * @return ProductChantierOrderLine
     */
    public function setProductChantierOrder(\Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder)
    {
        $this->productChantierOrder = $productChantierOrder;

        return $this;
    }

    /**
     * Get productChantierOrder.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductChantierOrder
     */
    public function getProductChantierOrder()
    {
        return $this->productChantierOrder;
    }
}
