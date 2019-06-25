<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PriceListD3E
 *
 * @ORM\Table(name="priceListD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PriceListD3ERepository")
 */
class PriceListD3E
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

//    /**
//     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", mappedBy="priceListD3E", cascade={"all"})
//     */
//    private $productD3Es;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\PriceListLineD3E", mappedBy="priceListD3E", cascade={"all"})
     */
    private $priceListLineD3Es;

    /**
     * Constructor
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3Es = new ArrayCollection();
        $this->priceListLineD3Es = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * Set name.
     *
     * @param string $name
     *
     * @return PriceListD3E
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
     * Add productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return PriceListD3E
     */
    public function addProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E)
    {
        $this->productD3Es[] = $productD3E;

        return $this;
    }

    /**
     * Remove productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E)
    {
        return $this->productD3Es->removeElement($productD3E);
    }

    /**
     * Get productD3Es.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3Es()
    {
        return $this->productD3Es;
    }

    /**
     * Add priceListLineD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\PriceListLineD3E $priceListLineD3E
     *
     * @return PriceListD3E
     */
    public function addPriceListLineD3E(\Paprec\CatalogBundle\Entity\PriceListLineD3E $priceListLineD3E)
    {
        $this->priceListLineD3Es[] = $priceListLineD3E;

        return $this;
    }

    /**
     * Remove priceListLineD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\PriceListLineD3E $priceListLineD3E
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePriceListLineD3E(\Paprec\CatalogBundle\Entity\PriceListLineD3E $priceListLineD3E)
    {
        return $this->priceListLineD3Es->removeElement($priceListLineD3E);
    }

    /**
     * Get priceListLineD3Es.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPriceListLineD3Es()
    {
        return $this->priceListLineD3Es;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return PriceListD3E
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
     * @return PriceListD3E
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
     * @return PriceListD3E
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
}
