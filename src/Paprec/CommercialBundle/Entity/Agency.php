<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Agency
 *
 * @ORM\Table(name="agencies")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\AgencyRepository")
 */
class Agency
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
     * @var array
     *
     * @ORM\Column(name="divisions", type="simple_array", nullable=true)
     * @Assert\NotBlank()
     */
    private $divisions;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^((\+)?33|0)[1-9](\d{2}){4}$/",
     *     match=true,
     *     message="Le n° de téléphone doit être au format français (ex: +33601020304, 0601020304)"
     * )
     */
    private $phoneNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=18, scale=15, nullable=true)
     * @Assert\NotBlank()
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=18, scale=15, nullable=true)
     * @Assert\NotBlank()
     */
    private $longitude;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;

    /** #########################
     *
     *  RELATIONS
     * ########################## */
    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\PriceListLineD3E", mappedBy="agency", cascade={"all"})
     */
    private $priceListLineD3Es;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuote", mappedBy="agency")
     */
    private $productDIQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierQuote", mappedBy="agency")
     */
    private $productChantierQuotes;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuote", mappedBy="agency")
     */
    private $productD3EQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="agency")
     */
    private $quoteRequests;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate", mappedBy="agency")
     */
    private $quoteRequestNonCorporates;



    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productDIQuotes = new ArrayCollection();
        $this->productChantierQuotes = new ArrayCollection();
        $this->productD3EQuotes = new ArrayCollection();
        $this->quoteRequestNonCorporates = new ArrayCollection();
        $this->quoteRequests = new ArrayCollection();

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
     * @return Agency
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
     * Set address.
     *
     * @param string $address
     *
     * @return Agency
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return Agency
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return Agency
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }


    /**
     * Set latitude.
     *
     * @param float $latitude
     *
     * @return Agency
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param float $longitude
     *
     * @return Agency
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set isDisplayed.
     *
     * @param bool $isDisplayed
     *
     * @return Agency
     */
    public function setIsDisplayed($isDisplayed)
    {
        $this->isDisplayed = $isDisplayed;

        return $this;
    }

    /**
     * Get isDisplayed.
     *
     * @return bool
     */
    public function getIsDisplayed()
    {
        return $this->isDisplayed;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Agency
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
     * @return Agency
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
     * @return Agency
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
     * Set divisions.
     *
     * @param array|null $divisions
     *
     * @return Agency
     */
    public function setDivisions($divisions = null)
    {
        $this->divisions = $divisions;

        return $this;
    }

    /**
     * Get divisions.
     *
     * @return array|null
     */
    public function getDivisions()
    {
        return $this->divisions;
    }


    /**
     * Add productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return Agency
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
     * Add quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return Agency
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

    /**
     * Add productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return Agency
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
     * Add productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return Agency
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
     * Add quoteRequestNonCorporate.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate
     *
     * @return Agency
     */
    public function addQuoteRequestNonCorporate(\Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $this->quoteRequestNonCorporates[] = $quoteRequestNonCorporate;

        return $this;
    }

    /**
     * Remove quoteRequestNonCorporate.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequestNonCorporate(\Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->quoteRequestNonCorporates->removeElement($quoteRequestNonCorporate);
    }

    /**
     * Get quoteRequestNonCorporates.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuoteRequestNonCorporates()
    {
        return $this->quoteRequestNonCorporates;
    }

    /**
     * Add priceListLineD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\PriceListLineD3E $priceListLineD3E
     *
     * @return Agency
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
     * Set phoneNumber.
     *
     * @param string|null $phoneNumber
     *
     * @return Agency
     */
    public function setPhoneNumber($phoneNumber = null)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber.
     *
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
}
