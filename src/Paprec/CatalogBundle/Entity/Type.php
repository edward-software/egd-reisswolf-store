<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Table(name="type")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\TypeRepository")
 */
class Type
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;


    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductD3EType", mappedBy="type", cascade={"all"})
     */
    private $productD3ETypes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuoteLine", mappedBy="type", cascade={"all"})
     */
    private $productD3EQuoteLines;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3ETypes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
     * @return Type
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
     * @return Type
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
     * @return Type
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
     * @return Type
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
     * Add productD3EType.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType
     *
     * @return Type
     */
    public function addProductD3EType(\Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType)
    {
        $this->productD3ETypes[] = $productD3EType;

        return $this;
    }

    /**
     * Remove productD3EType.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EType(\Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType)
    {
        return $this->productD3ETypes->removeElement($productD3EType);
    }

    /**
     * Get productD3ETypes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3ETypes()
    {
        return $this->productD3ETypes;
    }

    /**
     * Add productD3EQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine
     *
     * @return Type
     */
    public function addProductD3EQuoteLine(\Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine)
    {
        $this->productD3EQuoteLines[] = $productD3EQuoteLine;

        return $this;
    }

    /**
     * Remove productD3EQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EQuoteLine(\Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine)
    {
        return $this->productD3EQuoteLines->removeElement($productD3EQuoteLine);
    }

    /**
     * Get productD3EQuoteLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3EQuoteLines()
    {
        return $this->productD3EQuoteLines;
    }
}
