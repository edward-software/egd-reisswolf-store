<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $division;

    /**
     * @var string
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotBlank()
     */
    protected $enabled;

    /**
     * @ORM\Column(name="picto", type="string", nullable=true)
     */
    private $picto;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**************************************************************************************************
     * RELATIONS
     **************************************************************************************************/

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductDICategory", mappedBy="category",  cascade={"all"})
     */
    private $productDICategories;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductChantierCategory", mappedBy="category",  cascade={"all"})
     */
    private $productChantierCategories;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuoteLine", mappedBy="category", cascade={"all"})
     */
    private $productDIQuoteLines;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierQuoteLine", mappedBy="category", cascade={"all"})
     */
    private $productChantierQuoteLines;



    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productDICategories = new ArrayCollection();
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
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * Set name.
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set division.
     *
     * @param string|null $division
     *
     * @return Category
     */
    public function setDivision($division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return string|null
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return Category
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
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Category
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set picto.
     *
     * @param string|null $picto
     *
     * @return Category
     */
    public function setPicto($picto = null)
    {
        $this->picto = $picto;

        return $this;
    }

    /**
     * Get picto.
     *
     * @return string|null
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Category
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return Category
     */
    public function addProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        $this->productDICategories[] = $productDICategory;

        return $this;
    }

    /**
     * Remove productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        return $this->productDICategories->removeElement($productDICategory);
    }

    /**
     * Get productDICategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDICategories()
    {
        return $this->productDICategories;
    }

    /** ##############
     * Récupération des ProductCategories ordonnés par position
     *
     * ########### */

    /**
     * récupération des productDiCategories triés par ordre croissant de position
     */
    public function getProductDICategoriesOrdered()
    {
        $productDICategoriesToOrdered = $this->getProductDICategories()->getValues();
        usort($productDICategoriesToOrdered, array($this, 'compare'));
        $orderedItems = array();
        foreach ($productDICategoriesToOrdered as $orderedItem) {
            array_push($orderedItems, $orderedItem);
        }
        return $orderedItems;
    }

    /**
     * récupération des productChantierCategories triés par ordre croissant de position
     */
    public function getProductChantierCategoriesOrdered()
    {
        $productChantierCategoriesToOrdered = $this->getProductChantierCategories()->getValues();
        if (!empty($productChantierCategoriesToOrdered)) {
            usort($productChantierCategoriesToOrdered, array($this, 'compare'));
        }
        $orderedItems = array();
        foreach ($productChantierCategoriesToOrdered as $orderedItem) {
            array_push($orderedItems, $orderedItem);
        }
        return $orderedItems;
    }

    /**
     * Fonction de tri d'un tableau
     */
    private function compare($a, $b)
    {
        if ($a->getPosition() == $b->getPosition()) {
            return 0;
        }
        return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
    }

    /** ##############
     * FIN récupération des ProductCategories ordonnés par position
     * ########### */


    /**
     * Add productChantierCategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory
     *
     * @return Category
     */
    public function addProductChantierCategory(\Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory)
    {
        $this->productChantierCategories[] = $productChantierCategory;

        return $this;
    }

    /**
     * Remove productChantierCategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierCategory(\Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory)
    {
        return $this->productChantierCategories->removeElement($productChantierCategory);
    }

    /**
     * Get productChantierCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierCategories()
    {
        return $this->productChantierCategories;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Add productDIQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine
     *
     * @return Category
     */
    public function addProductDIQuoteLine(\Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine)
    {
        $this->productDIQuoteLines[] = $productDIQuoteLine;

        return $this;
    }

    /**
     * Remove productDIQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDIQuoteLine(\Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine)
    {
        return $this->productDIQuoteLines->removeElement($productDIQuoteLine);
    }

    /**
     * Get productDIQuoteLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDIQuoteLines()
    {
        return $this->productDIQuoteLines;
    }

    /**
     * Add productChantierQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuoteLine $productChantierQuoteLine
     *
     * @return Category
     */
    public function addProductChantierQuoteLine(\Paprec\CommercialBundle\Entity\ProductChantierQuoteLine $productChantierQuoteLine)
    {
        $this->productChantierQuoteLines[] = $productChantierQuoteLine;

        return $this;
    }

    /**
     * Remove productChantierQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuoteLine $productChantierQuoteLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierQuoteLine(\Paprec\CommercialBundle\Entity\ProductChantierQuoteLine $productChantierQuoteLine)
    {
        return $this->productChantierQuoteLines->removeElement($productChantierQuoteLine);
    }

    /**
     * Get productChantierQuoteLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierQuoteLines()
    {
        return $this->productChantierQuoteLines;
    }

}
