<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductChantierQuoteLine
 *
 * @ORM\Table(name="productChantierQuoteLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductChantierQuoteLineRepository")
 */
class ProductChantierQuoteLine
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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductChantier", inversedBy="productChantierQuoteLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productChantier;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Category", inversedBy="productChantierQuoteLines")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierQuote", inversedBy="productChantierQuoteLines")
     * @ORM\JoinColumn(name="productChantierQuoteId", referencedColumnName="id", nullable=false)
     */
    private $productChantierQuote;


    /**
     * ProductChantierQuoteLine constructor.
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
     * @return ProductChantierQuoteLine
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
     * @return ProductChantierQuoteLine
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
     * @return ProductChantierQuoteLine
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
     * @return ProductChantierQuoteLine
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
     * Set categoryName.
     *
     * @param string $categoryName
     *
     * @return ProductChantierQuoteLine
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
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return ProductChantierQuoteLine
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
     * @return ProductChantierQuoteLine
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
     * Set category.
     *
     * @param \Paprec\CatalogBundle\Entity\Category $category
     *
     * @return ProductChantierQuoteLine
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
     * Set productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return ProductChantierQuoteLine
     */
    public function setProductChantierQuote(\Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote)
    {
        $this->productChantierQuote = $productChantierQuote;

        return $this;
    }

    /**
     * Get productChantierQuote.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductChantierQuote
     */
    public function getProductChantierQuote()
    {
        return $this->productChantierQuote;
    }

    /**
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductChantierQuoteLine
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
     * @return ProductChantierQuoteLine
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
