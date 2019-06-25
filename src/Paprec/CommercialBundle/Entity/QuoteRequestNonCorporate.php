<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * QuoteRequestNonCorporate
 *
 * @ORM\Table(name="quoteRequestNonCorporates")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\QuoteRequestNonCorporateRepository")
 */
class QuoteRequestNonCorporate
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
     * @ORM\Column(name="civility", type="string", length=10)
     * @Assert\NotBlank()
     */
    private $civility;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstName;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *      message = "L'email '{{ value }}' n'a pas un format valide"
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Regex(
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
     * @Assert\NotBlank()
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
     * @ORM\Column(name="division", type="string", nullable=true)
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $postalCode;

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

    /**
     * @var string
     *
     * @ORM\Column(name="customerType", type="string", length=255)
     */
    private $customerType;

    /**
     * @var string
     *
     * @ORM\Column(name="locationsNumber", type="string", length=255, nullable=true)
     */
    private $locationsNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="concernedRegion", type="string", length=255, nullable=true)
     */
    private $concernedRegion;

    /** ###########################
     *
     *  RELATIONS
     *
     * ########################### */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="quoteRequestNonCorporates", cascade={"all"})
     * @ORM\JoinColumn(name="userInChargeId", referencedColumnName="id", nullable=true)
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="quoteRequestNonCorporates")
     * @ORM\JoinColumn(name="agencyId", referencedColumnName="id", nullable=true)
     */
    private $agency;


    /**
     * QuoteRequestNonCorporate constructor.
     * @throws \Exception
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
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * Set civility.
     *
     * @param string $civility
     *
     * @return QuoteRequestNonCorporate
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Get civility.
     *
     * @return string
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return QuoteRequestNonCorporate
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return QuoteRequestNonCorporate
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return QuoteRequestNonCorporate
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return QuoteRequestNonCorporate
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set quoteStatus.
     *
     * @param string $quoteStatus
     *
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * Set division.
     *
     * @param string|null $division
     *
     * @return QuoteRequestNonCorporate
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
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return QuoteRequestNonCorporate
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
     * Set associatedQuote.
     *
     * @param string|null $associatedQuote
     *
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * Set kookaburaNumber.
     *
     * @param int|null $kookaburaNumber
     *
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * @return QuoteRequestNonCorporate
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
     * Set function.
     *
     * @param string $function
     *
     * @return QuoteRequestNonCorporate
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function.
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set customerType.
     *
     * @param string $customerType
     *
     * @return QuoteRequestNonCorporate
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;

        return $this;
    }

    /**
     * Get customerType.
     *
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * Set locationsNumber.
     *
     * @param string $locationsNumber
     *
     * @return QuoteRequestNonCorporate
     */
    public function setLocationsNumber($locationsNumber)
    {
        $this->locationsNumber = $locationsNumber;

        return $this;
    }

    /**
     * Get locationsNumber.
     *
     * @return string
     */
    public function getLocationsNumber()
    {
        return $this->locationsNumber;
    }

    /**
     * Set generatedTurnover.
     *
     * @param string|null $generatedTurnover
     *
     * @return QuoteRequestNonCorporate
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
     * Set tonnage.
     *
     * @param string|null $tonnage
     *
     * @return QuoteRequestNonCorporate
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
     * Set businessName.
     *
     * @param string|null $businessName
     *
     * @return QuoteRequestNonCorporate
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
     * Set concernedRegion.
     *
     * @param string|null $concernedRegion
     *
     * @return QuoteRequestNonCorporate
     */
    public function setConcernedRegion($concernedRegion = null)
    {
        $this->concernedRegion = $concernedRegion;

        return $this;
    }

    /**
     * Get concernedRegion.
     *
     * @return string|null
     */
    public function getConcernedRegion()
    {
        return $this->concernedRegion;
    }
}
