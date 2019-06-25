<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3EQuoteLine
 *
 * @ORM\Table(name="productD3EQuoteLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductD3EQuoteLineRepository")
 */
class ProductD3EQuoteLine
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
     * @ORM\Column(name="typeName", type="string", length=255)
     */
    private $typeName;

    /**
     * @var int
     *
     * @ORM\Column(name="unitPrice", type="integer")
     */
    private $unitPrice;

    /**
     * @var boolean
     * Option de manutention sélectionnée
     * @ORM\Column(name="optHandling", type="boolean", options={"default" : false})
     */
    private $optHandling;

    /**
     * @var boolean
     * option de relevé de numéro de série sélectionnée
     * @ORM\Column(name="optSerialNumberStmt", type="boolean", options={"default" : false})
     */
    private $optSerialNumberStmt;

    /**
     * @var boolean
     * option de de destruction par broyage sélectionnée
     * @ORM\Column(name="optDestruction", type="boolean", options={"default" : false})
     */
    private $optDestruction;

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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", inversedBy="productD3EQuoteLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productD3E;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuote", inversedBy="productD3EQuoteLines")
     * @ORM\JoinColumn(name="productD3EQuoteId", referencedColumnName="id", nullable=false)
     */
    private $productD3EQuote;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Type", inversedBy="productD3EQuoteLines")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id", nullable=false)
     */
    private $type;


    /**
     * ProductD3EQuoteLine constructor.
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
     * @return ProductD3EQuoteLine
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
     * @return ProductD3EQuoteLine
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
     * @return ProductD3EQuoteLine
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
     * @return ProductD3EQuoteLine
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
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return ProductD3EQuoteLine
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
     * @return ProductD3EQuoteLine
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
     * Set productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return ProductD3EQuoteLine
     */
    public function setProductD3EQuote(\Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote)
    {
        $this->productD3EQuote = $productD3EQuote;

        return $this;
    }

    /**
     * Get productD3EQuote.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductD3EQuote
     */
    public function getProductD3EQuote()
    {
        return $this->productD3EQuote;
    }

    /**
     * Set optHandling.
     *
     * @param bool $optHandling
     *
     * @return ProductD3EQuoteLine
     */
    public function setOptHandling($optHandling)
    {
        $this->optHandling = $optHandling;

        return $this;
    }

    /**
     * Get optHandling.
     *
     * @return bool
     */
    public function getOptHandling()
    {
        return $this->optHandling;
    }

    /**
     * Set optSerialNumberStmt.
     *
     * @param bool $optSerialNumberStmt
     *
     * @return ProductD3EQuoteLine
     */
    public function setOptSerialNumberStmt($optSerialNumberStmt)
    {
        $this->optSerialNumberStmt = $optSerialNumberStmt;

        return $this;
    }

    /**
     * Get optSerialNumberStmt.
     *
     * @return bool
     */
    public function getOptSerialNumberStmt()
    {
        return $this->optSerialNumberStmt;
    }

    /**
     * Set optDestruction.
     *
     * @param bool $optDestruction
     *
     * @return ProductD3EQuoteLine
     */
    public function setOptDestruction($optDestruction)
    {
        $this->optDestruction = $optDestruction;

        return $this;
    }

    /**
     * Get optDestruction.
     *
     * @return bool
     */
    public function getOptDestruction()
    {
        return $this->optDestruction;
    }

    /**
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductD3EQuoteLine
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
     * @return ProductD3EQuoteLine
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
     * Set typeName.
     *
     * @param string $typeName
     *
     * @return ProductD3EQuoteLine
     */
    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;

        return $this;
    }

    /**
     * Get typeName.
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * Set type.
     *
     * @param \Paprec\CatalogBundle\Entity\Type $type
     *
     * @return ProductD3EQuoteLine
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
