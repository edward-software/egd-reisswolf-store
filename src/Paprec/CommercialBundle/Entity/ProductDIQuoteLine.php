<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductDIQuoteLine
 *
 * @ORM\Table(name="productDIQuoteLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductDIQuoteLineRepository")
 */
class ProductDIQuoteLine
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
     * @ORM\Column(name="categoryName", type="string", length=255)
     */
    private $categoryName;

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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductDI", inversedBy="productDIQuoteLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productDI;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Category", inversedBy="productDIQuoteLines")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuote", inversedBy="productDIQuoteLines")
     * @ORM\JoinColumn(name="productDIQuoteId", referencedColumnName="id", nullable=false)
     */
    private $productDIQuote;


    /**
     * ProductDIQuoteLine constructor.
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
     * @return ProductDIQuoteLine
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
     * @return ProductDIQuoteLine
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
     * @return ProductDIQuoteLine
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
     * @return ProductDIQuoteLine
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
     * @return ProductDIQuoteLine
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
     * Set productDI.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI $productDI
     *
     * @return ProductDIQuoteLine
     */
    public function setProductDI(\Paprec\CatalogBundle\Entity\ProductDI $productDI)
    {
        $this->productDI = $productDI;

        return $this;
    }

    /**
     * Get productDI.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductDI
     */
    public function getProductDI()
    {
        return $this->productDI;
    }

    /**
     * Set productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return ProductDIQuoteLine
     */
    public function setProductDIQuote(\Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote)
    {
        $this->productDIQuote = $productDIQuote;

        return $this;
    }

    /**
     * Get productDIQuote.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductDIQuote
     */
    public function getProductDIQuote()
    {
        return $this->productDIQuote;
    }


    /**
     * Set category.
     *
     * @param \Paprec\CatalogBundle\Entity\Category $category
     *
     * @return ProductDIQuoteLine
     */
    public function setCategory(\Paprec\CatalogBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \Paprec\CatalogBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set categoryName.
     *
     * @param string $categoryName
     *
     * @return ProductDIQuoteLine
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get categoryName.
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductDIQuoteLine
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
     * @return ProductDIQuoteLine
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
}
