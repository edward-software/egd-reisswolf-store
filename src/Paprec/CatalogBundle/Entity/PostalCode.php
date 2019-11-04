<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostalCode
 *
 * @ORM\Table(name="postalCodes")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PostalCodeRepository")
 * @UniqueEntity(fields={"code"}, repositoryMethod="isCodeUnique")
 */
class PostalCode
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
     * #################################
     *              SYSTEM USER ASSOCIATION
     * #################################
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=false)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var text
     * @ORM\Column(name="code", type="string")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{2}(\*|(?:\d{2}))$/",
     *     match=true,
     *     message="Le codes postal doivent être un nombre de 4 caractères ou 2 suivis d'une *. (ex: 15*, 1530)"
     * )
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="setUpRate", type="bigint")
     * @Assert\NotBlank()
     */
    private $setUpRate;

    /**
     * @var int
     *
     * @ORM\Column(name="rentalRate", type="bigint")
     * @Assert\NotBlank()
     */
    private $rentalRate;

    /**
     * @var int
     *
     * @ORM\Column(name="transportRate", type="bigint")
     * @Assert\NotBlank()
     */
    private $transportRate;

    /**
     * @var int
     *
     * @ORM\Column(name="treatmentRate", type="bigint")
     * @Assert\NotBlank()
     */
    private $treatmentRate;


    /**
     * @var int
     *
     * @ORM\Column(name="traceabilityRate", type="bigint")
     * @Assert\NotBlank()
     */
    private $traceabilityRate;


    /**
     * @var text
     * @ORM\Column(name="city", type="string")
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var text
     * @ORM\Column(name="zone", type="string")
     * @Assert\NotBlank()
     */
    private $zone;


    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="postalCodes")
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Region", inversedBy="postalCodes")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="postalCode")
     */
    private $quoteRequests;

    /**
     * Constructor
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
     * @return PostalCode
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
     * @return PostalCode
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
     * @return PostalCode
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
     * Set code.
     *
     * @param string $code
     *
     * @return PostalCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set transportRate.
     *
     * @param int $transportRate
     *
     * @return PostalCode
     */
    public function setTransportRate($transportRate)
    {
        $this->transportRate = $transportRate;

        return $this;
    }

    /**
     * Get transportRate.
     *
     * @return int
     */
    public function getTransportRate()
    {
        return $this->transportRate;
    }

    /**
     * Set treatmentRate.
     *
     * @param int $treatmentRate
     *
     * @return PostalCode
     */
    public function setTreatmentRate($treatmentRate)
    {
        $this->treatmentRate = $treatmentRate;

        return $this;
    }

    /**
     * Get treatmentRate.
     *
     * @return int
     */
    public function getTreatmentRate()
    {
        return $this->treatmentRate;
    }

    /**
     * Set traceabilityRate.
     *
     * @param int $traceabilityRate
     *
     * @return PostalCode
     */
    public function setTraceabilityRate($traceabilityRate)
    {
        $this->traceabilityRate = $traceabilityRate;

        return $this;
    }

    /**
     * Get traceabilityRate.
     *
     * @return int
     */
    public function getTraceabilityRate()
    {
        return $this->traceabilityRate;
    }

    /**
     * Set userCreation.
     *
     * @param \Paprec\UserBundle\Entity\User $userCreation
     *
     * @return PostalCode
     */
    public function setUserCreation(\Paprec\UserBundle\Entity\User $userCreation)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation.
     *
     * @return \Paprec\UserBundle\Entity\User
     */
    public function getUserCreation()
    {
        return $this->userCreation;
    }

    /**
     * Set userUpdate.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userUpdate
     *
     * @return PostalCode
     */
    public function setUserUpdate(\Paprec\UserBundle\Entity\User $userUpdate = null)
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * Get userUpdate.
     *
     * @return \Paprec\UserBundle\Entity\User|null
     */
    public function getUserUpdate()
    {
        return $this->userUpdate;
    }

    /**
     * Set userInCharge.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userInCharge
     *
     * @return PostalCode
     */
    public function setUserInCharge(\Paprec\UserBundle\Entity\User $userInCharge = null)
    {
        $this->userInCharge = $userInCharge;

        return $this;
    }

    /**
     * Get userInCharge.
     *
     * @return \Paprec\UserBundle\Entity\User|null
     */
    public function getUserInCharge()
    {
        return $this->userInCharge;
    }

    /**
     * Set region.
     *
     * @param \Paprec\CatalogBundle\Entity\Region|null $region
     *
     * @return PostalCode
     */
    public function setRegion(\Paprec\CatalogBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return \Paprec\CatalogBundle\Entity\Region|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return PostalCode
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
     * Set zone.
     *
     * @param string $zone
     *
     * @return PostalCode
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone.
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Add quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return PostalCode
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
     * Set setUpRate.
     *
     * @param int $setUpRate
     *
     * @return PostalCode
     */
    public function setSetUpRate($setUpRate)
    {
        $this->setUpRate = $setUpRate;

        return $this;
    }

    /**
     * Get setUpRate.
     *
     * @return int
     */
    public function getSetUpRate()
    {
        return $this->setUpRate;
    }

    /**
     * Set rentalRate.
     *
     * @param int $rentalRate
     *
     * @return PostalCode
     */
    public function setRentalRate($rentalRate)
    {
        $this->rentalRate = $rentalRate;

        return $this;
    }

    /**
     * Get rentalRate.
     *
     * @return int
     */
    public function getRentalRate()
    {
        return $this->rentalRate;
    }
}
