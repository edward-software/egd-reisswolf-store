<?php

namespace Paprec\PublicBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="carts")
 * @ORM\Entity(repositoryClass="Paprec\PublicBundle\Repository\CartRepository")
 */
class Cart
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="disabled", type="datetime", nullable=true)
     */
    private $disabled;


    /**
     * @var string
     *
     * @ORM\Column(name="division", type="string", length=255, nullable=true)
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=18, scale=15, nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=18, scale=15, nullable=true)
     */
    private $longitude;
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;


    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=255, nullable=true)
     */
    private $frequency;

    /**
     * 'Order' ou 'Quote' ou 'null' pour DI
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(name="displayedCategories", type="simple_array", nullable=true)
     */
    private $displayedCategories;

    /**
     * @var array
     *
     * @ORM\Column(name="displayedProducts", type="array", nullable=true)
     */
    private $displayedProducts;

    /**
     * @var array|null
     *
     * @ORM\Column(name="content", type="json", nullable=true)
     */
    private $content;

    /**
     * Etape dans lequel se trouve le Cart (créé, division choisie, livraison,...)
     *
     * @var string
     *
     * @ORM\Column(name="step", type="string", length=255, nullable=true)
     */
    private $step;


    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->setDisplayedProducts = array();
        $this->content = array();
    }

    /**
     * Get id.
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
     * @return Cart
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
     * @return Cart
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
     * Set division.
     *
     * @param string $division
     *
     * @return Cart
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return string
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set location.
     *
     * @param string $location
     *
     * @return Cart
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set frequency.
     *
     * @param string $frequency
     *
     * @return Cart
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency.
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set displayedCategories.
     *
     * @param array|null $displayedCategories
     *
     * @return Cart
     */
    public function setDisplayedCategories($displayedCategories = null)
    {
        $this->displayedCategories = $displayedCategories;

        return $this;
    }

    /**
     * Get displayedCategories.
     *
     * @return array|null
     */
    public function getDisplayedCategories()
    {
        return $this->displayedCategories;
    }

    /**
     * Set displayedProducts.
     *
     * @param array|null $displayedProducts
     *
     * @return Cart
     */
    public function setDisplayedProducts($displayedProducts = null)
    {
        $this->displayedProducts = $displayedProducts;

        return $this;
    }

    /**
     * Get displayedProducts.
     *
     * @return array|null
     */
    public function getDisplayedProducts()
    {
        return $this->displayedProducts;
    }

    /**
     * Set content.
     *
     * @param json|null $content
     *
     * @return Cart
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return json|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return Cart
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set city.
     *
     * @param string|null $city
     *
     * @return Cart
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postalCode.
     *
     * @param string|null $postalCode
     *
     * @return Cart
     */
    public function setPostalCode($postalCode = null)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return Cart
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string|null $longitude
     *
     * @return Cart
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set disabled.
     *
     * @param \DateTime|null $disabled
     *
     * @return Cart
     */
    public function setDisabled($disabled = null)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled.
     *
     * @return \DateTime|null
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set step.
     *
     * @param string|null $step
     *
     * @return Cart
     */
    public function setStep($step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step.
     *
     * @return string|null
     */
    public function getStep()
    {
        return $this->step;
    }
}
