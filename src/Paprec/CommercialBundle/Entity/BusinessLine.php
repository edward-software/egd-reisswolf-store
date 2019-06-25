<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * BusinessLine
 *
 * @ORM\Table(name="businessLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\BusinessLineRepository")
 */
class BusinessLine
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
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuote", mappedBy="businessLine")
     */
    private $productDIQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierQuote", mappedBy="businessLine")
     */
    private $productChantierQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuote", mappedBy="businessLine")
     */
    private $productD3EQuotes;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierOrder", mappedBy="businessLine")
     */
    private $productChantierOrders;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EOrder", mappedBy="businessLine")
     */
    private $productD3EOrders;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="businessLine")
     */
    private $quoteRequests;

    /**
     * BusinessLine constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productDIQuotes = new ArrayCollection();
        $this->productChantierQuotes = new ArrayCollection();
        $this->productD3EQuotes = new ArrayCollection();
        $this->productChantierOrders = new ArrayCollection();
        $this->productD3EOrders = new ArrayCollection();
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
     * @return BusinessLine
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
     * @return BusinessLine
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
     * @return BusinessLine
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
     * @return BusinessLine
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
     * @return BusinessLine
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
     * Add productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return BusinessLine
     */
    public function addProductDIQuote(\Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote)
    {
        $this->productDIQuotes[] = $productDIQuote;

        return $this;
    }

    /**
     * Remove productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDIQuote(\Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote)
    {
        return $this->productDIQuotes->removeElement($productDIQuote);
    }

    /**
     * Get productDIQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDIQuotes()
    {
        return $this->productDIQuotes;
    }

    /**
     * Add productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return BusinessLine
     */
    public function addProductChantierQuote(\Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote)
    {
        $this->productChantierQuotes[] = $productChantierQuote;

        return $this;
    }

    /**
     * Remove productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierQuote(\Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote)
    {
        return $this->productChantierQuotes->removeElement($productChantierQuote);
    }

    /**
     * Get productChantierQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierQuotes()
    {
        return $this->productChantierQuotes;
    }

    /**
     * Add productChantierOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder
     *
     * @return BusinessLine
     */
    public function addProductChantierOrder(\Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder)
    {
        $this->productChantierOrders[] = $productChantierOrder;

        return $this;
    }

    /**
     * Remove productChantierOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierOrder(\Paprec\CommercialBundle\Entity\ProductChantierOrder $productChantierOrder)
    {
        return $this->productChantierOrders->removeElement($productChantierOrder);
    }

    /**
     * Get productChantierOrders.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierOrders()
    {
        return $this->productChantierOrders;
    }

    /**
     * Add productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return BusinessLine
     */
    public function addProductD3EQuote(\Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote)
    {
        $this->productD3EQuotes[] = $productD3EQuote;

        return $this;
    }

    /**
     * Remove productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EQuote(\Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote)
    {
        return $this->productD3EQuotes->removeElement($productD3EQuote);
    }

    /**
     * Get productD3EQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3EQuotes()
    {
        return $this->productD3EQuotes;
    }

    /**
     * Add productD3EOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder
     *
     * @return BusinessLine
     */
    public function addProductD3EOrder(\Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder)
    {
        $this->productD3EOrders[] = $productD3EOrder;

        return $this;
    }

    /**
     * Remove productD3EOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EOrder(\Paprec\CommercialBundle\Entity\ProductD3EOrder $productD3EOrder)
    {
        return $this->productD3EOrders->removeElement($productD3EOrder);
    }

    /**
     * Get productD3EOrders.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3EOrders()
    {
        return $this->productD3EOrders;
    }

    /**
     * Add quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return BusinessLine
     */
    public function addQuoteRequest(\Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest)
    {
        $this->quoteRequests[] = $quoteRequest;

        return $this;
    }

    /**
     * Remove quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequest(\Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest)
    {
        return $this->quoteRequests->removeElement($quoteRequest);
    }

    /**
     * Get quoteRequests.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuoteRequests()
    {
        return $this->quoteRequests;
    }
}
