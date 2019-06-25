<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3EQuote
 *
 * @ORM\Table(name="productD3EQuotes")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductD3EQuoteRepository")
 */
class ProductD3EQuote
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
     * @ORM\Column(name="businessName", type="string", length=255)
     * @Assert\NotBlank()
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
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     */
    private $function;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *      message = "Le format de l'email est invalide"
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text")
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="headoffice_address", type="text", nullable=true)
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
     */
    private $headoffice_city;


    /**
     * @var string
     *
     * @ORM\Column(name="preferredContact", type="string", length=10, nullable=true)
     * @Assert\NotBlank()
     */
    private $preferredContact;

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
     * @ORM\Column(name="quoteStatus", type="string", length=255)
     */
    private $quoteStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="totalAmount", type="integer", nullable=true)
     */
    private $totalAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="generatedTurnover", type="string", length=20, nullable=true)
     */
    private $generatedTurnover;

    /**
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
     * @ORM\Column(name="tonnage", type="string",length=50, nullable=true)
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
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuoteLine", mappedBy="productD3EQuote", cascade={"all"})
     */
    private $productD3EQuoteLines;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="productD3EQuotes", cascade={"all"})
     * @ORM\JoinColumn(name="userInChargeId", referencedColumnName="id", nullable=true)
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="productD3EQuotes")
     * @ORM\JoinColumn(name="agencyId", referencedColumnName="id", nullable=true)
     */
    private $agency;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\BusinessLine", inversedBy="productD3EQuotes")
     * @ORM\JoinColumn(name="businessLineId", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $businessLine;


    /**
     * ProductDIQuote constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3EQuoteLines = new ArrayCollection();
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @param string $businessName
     *
     * @return ProductD3EQuote
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;

        return $this;
    }

    /**
     * Get businessName.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * Set civility.
     *
     * @param string $civility
     *
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * Set function.
     *
     * @param string|null $function
     *
     * @return ProductD3EQuote
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
     * Set email.
     *
     * @param string $email
     *
     * @return ProductD3EQuote
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
     * Set address.
     *
     * @param string $address
     *
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * Set headofficeAddress.
     *
     * @param string|null $headofficeAddress
     *
     * @return ProductD3EQuote
     */
    public function setHeadofficeAddress($headofficeAddress = null)
    {
        $this->headoffice_address = $headofficeAddress;

        return $this;
    }

    /**
     * Get headofficeAddress.
     *
     * @return string|null
     */
    public function getHeadofficeAddress()
    {
        return $this->headoffice_address;
    }

    /**
     * Set headofficePostalCode.
     *
     * @param string|null $headofficePostalCode
     *
     * @return ProductD3EQuote
     */
    public function setHeadofficePostalCode($headofficePostalCode = null)
    {
        $this->headoffice_postalCode = $headofficePostalCode;

        return $this;
    }

    /**
     * Get headofficePostalCode.
     *
     * @return string|null
     */
    public function getHeadofficePostalCode()
    {
        return $this->headoffice_postalCode;
    }

    /**
     * Set headofficeCity.
     *
     * @param string|null $headofficeCity
     *
     * @return ProductD3EQuote
     */
    public function setHeadofficeCity($headofficeCity = null)
    {
        $this->headoffice_city = $headofficeCity;

        return $this;
    }

    /**
     * Get headofficeCity.
     *
     * @return string|null
     */
    public function getHeadofficeCity()
    {
        return $this->headoffice_city;
    }

    /**
     * Set preferredContact.
     *
     * @param string|null $preferredContact
     *
     * @return ProductD3EQuote
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

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * Set totalAmount.
     *
     * @param int|null $totalAmount
     *
     * @return ProductD3EQuote
     */
    public function setTotalAmount($totalAmount = null)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount.
     *
     * @return int|null
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set generatedTurnover.
     *
     * @param string|null $generatedTurnover
     *
     * @return ProductD3EQuote
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
     * Set summary.
     *
     * @param string|null $summary
     *
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * Add productD3EQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine
     *
     * @return ProductD3EQuote
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

    /**
     * Set userInCharge.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userInCharge
     *
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
     * @return ProductD3EQuote
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
}
