<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductDICategoryType
 *
 * @ORM\Table(name="productDICategories")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductDICategoryRepository")
 */
class ProductDICategory
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
     * @ORM\Column(name="position", type="integer")
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="unitPrice", type="integer")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $unitPrice;


    /**************************************************************************************************
     * RELATIONS
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductDI", inversedBy="productDICategories")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productDI;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Category", inversedBy="productDICategories")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id", nullable=false)
     */
    private $category;

    public function __construct()
    {
        $this->setPosition(1000);
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
     * Set position.
     *
     * @param int $position
     *
     * @return ProductDICategory
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set productDI.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI $productDI
     *
     * @return ProductDICategory
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
     * Set category.
     *
     * @param \Paprec\CatalogBundle\Entity\Category $category
     *
     * @return ProductDICategory
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

    public function __toString()
    {
        return $this->category . ' ' . $this->position;
    }

    /**
     * Set unitPrice.
     *
     * @param int $unitPrice
     *
     * @return ProductDICategory
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
}
