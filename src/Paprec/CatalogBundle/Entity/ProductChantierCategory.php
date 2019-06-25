<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductChantierCategory
 *
 * @ORM\Table(name="productChantierCategories")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductChantierCategoryRepository")
 */
class ProductChantierCategory
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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductChantier", inversedBy="productChantierCategories")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productChantier;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Category", inversedBy="productChantierCategories")
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
     * @return ProductChantierCategory
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
     * Set productChantier.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantier $productChantier
     *
     * @return ProductChantierCategory
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
     * @return ProductChantierCategory
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
     * @return ProductChantierCategory
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
