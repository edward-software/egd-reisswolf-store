<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * QuoteRequest
 *
 * @ORM\Table(name="quoteRequests")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\QuoteRequestRepository")
 */
class QuoteRequest
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
     * @ORM\Column(name="businessName", type="string", length=255, nullable=true)
     */
    private $businessName;


    /**
     * @var string
     *
     * @ORM\Column(name="civility", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $civility;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $firstName;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(
     *      groups={"details"},
     *      message = "L'email '{{ value }}' n'a pas un format valide"
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"details"})
     * @Assert\Regex(
     *     groups={"details"},
     *     pattern="/^((\+)?33|0)[1-9](\d{2}){4}$/",
     *     match=true,
     *     message="Le n° de téléphone doit être au format français (ex: +33601020304, 0601020304)"
     * )
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="quoteStatus", type="string", length=255)
     */
    private $quoteStatus;

    /**
     * "Mon besoin" rempli par l'utilisateur Front Office
     * @var string
     *
     * @ORM\Column(name="need", type="text")
     * @Assert\NotBlank(groups={"need"})
     */
    private $need;

    /**
     * @var array
     *
     * @ORM\Column(name="attachedFiles", type="array", nullable=true)
     */
    private $attachedFiles;


    /**
     * @var string
     *
     * @ORM\Column(name="generatedTurnover", type="string", length=20, nullable=true)
     */
    private $generatedTurnover;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string")
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255)
     */
    private $postalCode;


    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="headoffice_address", type="text", nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $headoffice_address;

    /**
     * @var string
     *
     * @ORM\Column(name="headoffice_postalCode", type="string", length=255, nullable=true)
     */
    private $headoffice_postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="headoffice_city", type="text", nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $headoffice_city;

    /**
     * @var string
     *
     * @ORM\Column(name="preferredContact", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"details"})
     */
    private $preferredContact;


    /**
     * Devis associé
     * @var string
     *
     * @ORM\Column(name="associatedQuote", type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $associatedQuote;

    /**
     * Résumé du besoin rempli par le commercial
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=10, nullable=true)
     */
    private $frequency;

    /**
     * @var string
     *
     * @ORM\Column(name="tonnage", type="string", length=50, nullable=true)
     */
    private $tonnage;

    /**
     * @var integer
     *
     * @ORM\Column(name="kookaburaNumber", type="integer", nullable=true)
     */
    private $kookaburaNumber;


    /** ###########################
     *
     *  RELATIONS
     *
     * ########################### */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="quoteRequests", cascade={"all"})
     * @ORM\JoinColumn(name="userInChargeId", referencedColumnName="id", nullable=true)
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="quoteRequests")
     * @ORM\JoinColumn(name="agencyId", referencedColumnName="id", nullable=true)
     */
    private $agency;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\BusinessLine", inversedBy="quoteRequests")
     * @ORM\JoinColumn(name="businessLineId", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $businessLine;


    /**
     * QuoteRequest constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->attachedFiles = array();
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * Set businessName.
     *
     * @param string|null $businessName
     *
     * @return QuoteRequest
     */
    public function setBusinessName($businessName = null)
    {
        $this->businessName = $businessName;

        return $this;
    }

    /**
     * Get businessName.
     *
     * @return string|null
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * Set civility.
     *
     * @param string|null $civility
     *
     * @return QuoteRequest
     */
    public function setCivility($civility = null)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Get civility.
     *
     * @return string|null
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * Set lastName.
     *
     * @param string|null $lastName
     *
     * @return QuoteRequest
     */
    public function setLastName($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
     * @param string|null $firstName
     *
     * @return QuoteRequest
     */
    public function setFirstName($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return QuoteRequest
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone.
     *
     * @param string|null $phone
     *
     * @return QuoteRequest
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set function.
     *
     * @param string|null $function
     *
     * @return QuoteRequest
     */
    public function setFunction($function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function.
     *
     * @return string|null
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set quoteStatus.
     *
     * @param string $quoteStatus
     *
     * @return QuoteRequest
     */
    public function setQuoteStatus($quoteStatus)
    {
        $this->quoteStatus = $quoteStatus;

        return $this;
    }

    /**
     * Get quoteStatus.
     *
     * @return string
     */
    public function getQuoteStatus()
    {
        return $this->quoteStatus;
    }

    /**
     * Set need.
     *
     * @param string $need
     *
     * @return QuoteRequest
     */
    public function setNeed($need)
    {
        $this->need = $need;

        return $this;
    }

    /**
     * Get need.
     *
     * @return string
     */
    public function getNeed()
    {
        return $this->need;
    }

    /**
     * Set attachedFiles.
     *
     * @param array|null $attachedFiles
     *
     * @return QuoteRequest
     */
    public function setAttachedFiles($attachedFiles = null)
    {
        $this->attachedFiles = $attachedFiles;

        return $this;
    }

    /**
     * Get attachedFiles.
     *
     * @return array|null
     */
    public function getAttachedFiles()
    {
        return $this->attachedFiles;
    }

    /**
     * Set generatedTurnover.
     *
     * @param string|null $generatedTurnover
     *
     * @return QuoteRequest
     */
    public function setGeneratedTurnover($generatedTurnover = null)
    {
        $this->generatedTurnover = $generatedTurnover;

        return $this;
    }

    /**
     * Get generatedTurnover.
     *
     * @return string|null
     */
    public function getGeneratedTurnover()
    {
        return $this->generatedTurnover;
    }

    /**
     * Set division.
     *
     * @param string $division
     *
     * @return QuoteRequest
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
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * Set address.
     *
     * @param string $address
     *
     * @return QuoteRequest
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
     * Set headofficeAddress.
     *
     * @param string $headofficeAddress
     *
     * @return QuoteRequest
     */
    public function setHeadofficeAddress($headofficeAddress)
    {
        $this->headoffice_address = $headofficeAddress;

        return $this;
    }

    /**
     * Get headofficeAddress.
     *
     * @return string
     */
    public function getHeadofficeAddress()
    {
        return $this->headoffice_address;
    }

    /**
     * Set headofficePostalCode.
     *
     * @param string $headofficePostalCode
     *
     * @return QuoteRequest
     */
    public function setHeadofficePostalCode($headofficePostalCode)
    {
        $this->headoffice_postalCode = $headofficePostalCode;

        return $this;
    }

    /**
     * Get headofficePostalCode.
     *
     * @return string
     */
    public function getHeadofficePostalCode()
    {
        return $this->headoffice_postalCode;
    }

    /**
     * Set headofficeCity.
     *
     * @param string $headofficeCity
     *
     * @return QuoteRequest
     */
    public function setHeadofficeCity($headofficeCity)
    {
        $this->headoffice_city = $headofficeCity;

        return $this;
    }

    /**
     * Get headofficeCity.
     *
     * @return string
     */
    public function getHeadofficeCity()
    {
        return $this->headoffice_city;
    }

    /**
     * Set associatedQuote.
     *
     * @param string|null $associatedQuote
     *
     * @return QuoteRequest
     */
    public function setAssociatedQuote($associatedQuote = null)
    {
        $this->associatedQuote = $associatedQuote;

        return $this;
    }

    /**
     * Get associatedQuote.
     *
     * @return string|null
     */
    public function getAssociatedQuote()
    {
        return $this->associatedQuote;
    }

    /**
     * Set summary.
     *
     * @param string|null $summary
     *
     * @return QuoteRequest
     */
    public function setSummary($summary = null)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary.
     *
     * @return string|null
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set frequency.
     *
     * @param string|null $frequency
     *
     * @return QuoteRequest
     */
    public function setFrequency($frequency = null)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency.
     *
     * @return string|null
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set tonnage.
     *
     * @param string|null $tonnage
     *
     * @return QuoteRequest
     */
    public function setTonnage($tonnage = null)
    {
        $this->tonnage = $tonnage;

        return $this;
    }

    /**
     * Get tonnage.
     *
     * @return string|null
     */
    public function getTonnage()
    {
        return $this->tonnage;
    }

    /**
     * Set kookaburaNumber.
     *
     * @param int|null $kookaburaNumber
     *
     * @return QuoteRequest
     */
    public function setKookaburaNumber($kookaburaNumber = null)
    {
        $this->kookaburaNumber = $kookaburaNumber;

        return $this;
    }

    /**
     * Get kookaburaNumber.
     *
     * @return int|null
     */
    public function getKookaburaNumber()
    {
        return $this->kookaburaNumber;
    }

    /**
     * Set userInCharge.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userInCharge
     *
     * @return QuoteRequest
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
     * Set agency.
     *
     * @param \Paprec\CommercialBundle\Entity\Agency|null $agency
     *
     * @return QuoteRequest
     */
    public function setAgency(\Paprec\CommercialBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency.
     *
     * @return \Paprec\CommercialBundle\Entity\Agency|null
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set businessLine.
     *
     * @param \Paprec\CommercialBundle\Entity\BusinessLine|null $businessLine
     *
     * @return QuoteRequest
     */
    public function setBusinessLine(\Paprec\CommercialBundle\Entity\BusinessLine $businessLine = null)
    {
        $this->businessLine = $businessLine;

        return $this;
    }

    /**
     * Get businessLine.
     *
     * @return \Paprec\CommercialBundle\Entity\BusinessLine|null
     */
    public function getBusinessLine()
    {
        return $this->businessLine;
    }

    /**
     * Set preferredContact.
     *
     * @param string|null $preferredContact
     *
     * @return QuoteRequest
     */
    public function setPreferredContact($preferredContact = null)
    {
        $this->preferredContact = $preferredContact;

        return $this;
    }

    /**
     * Get preferredContact.
     *
     * @return string|null
     */
    public function getPreferredContact()
    {
        return $this->preferredContact;
    }
}
