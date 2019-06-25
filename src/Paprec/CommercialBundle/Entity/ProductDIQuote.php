<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductDIQuote
 *
 * @ORM\Table(name="productDIQuotes")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductDIQuoteRepository")
 */
class ProductDIQuote
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
     * @Assert\NotBlank()
     */
    private $address;

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
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuoteLine", mappedBy="productDIQuote", cascade={"all"})
     */
    private $productDIQuoteLines;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="productDIQuotes", cascade={"all"})
     * @ORM\JoinColumn(name="userInChargeId", referencedColumnName="id", nullable=true)
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="productDIQuotes")
     * @ORM\JoinColumn(name="agencyId", referencedColumnName="id", nullable=true)
     */
    private $agency;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\BusinessLine", inversedBy="productDIQuotes")
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
        $this->productDIQuoteLines = new ArrayCollection();
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
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return ProductDIQuote
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

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
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return ProductDIQuote
     */
    public function setDateUpdate($dateUpdate = null)
    {
        $this->dateUpdate = $dateUpdate;

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
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return ProductDIQuote
     */
    public function setDeleted($deleted = null)
    {
        $this->deleted = $deleted;

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
     * Set businessName.
     *
     * @param string $businessName
     *
     * @return ProductDIQuote
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;

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
     * Set civility.
     *
     * @param string $civility
     *
     * @return ProductDIQuote
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

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
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return ProductDIQuote
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

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
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return ProductDIQuote
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

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
     * Set function.
     *
     * @param string|null $function
     *
     * @return ProductDIQuote
     */
    public function setFunction($function = null)
    {
        $this->function = $function;

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
     * Set email.
     *
     * @param string $email
     *
     * @return ProductDIQuote
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return ProductDIQuote
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

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
     * Set city.
     *
     * @param string|null $city
     *
     * @return ProductDIQuote
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address.
     *
     * @param string|null $address
     *
     * @return ProductDIQuote
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

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
     * Set headofficeAddress.
     *
     * @param string|null $headofficeAddress
     *
     * @return ProductDIQuote
     */
    public function setHeadofficeAddress($headofficeAddress = null)
    {
        $this->headoffice_address = $headofficeAddress;

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
     * Set headofficePostalCode.
     *
     * @param string|null $headofficePostalCode
     *
     * @return ProductDIQuote
     */
    public function setHeadofficePostalCode($headofficePostalCode = null)
    {
        $this->headoffice_postalCode = $headofficePostalCode;

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
     * Set headofficeCity.
     *
     * @param string|null $headofficeCity
     *
     * @return ProductDIQuote
     */
    public function setHeadofficeCity($headofficeCity = null)
    {
        $this->headoffice_city = $headofficeCity;

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
     * Set preferredContact.
     *
     * @param string|null $preferredContact
     *
     * @return ProductDIQuote
     */
    public function setPreferredContact($preferredContact = null)
    {
        $this->preferredContact = $preferredContact;

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
     * Set phone.
     *
     * @param string $phone
     *
     * @return ProductDIQuote
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

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
     * Set quoteStatus.
     *
     * @param string $quoteStatus
     *
     * @return ProductDIQuote
     */
    public function setQuoteStatus($quoteStatus)
    {
        $this->quoteStatus = $quoteStatus;

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
     * Set totalAmount.
     *
     * @param int|null $totalAmount
     *
     * @return ProductDIQuote
     */
    public function setTotalAmount($totalAmount = null)
    {
        $this->totalAmount = $totalAmount;

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
     * Set generatedTurnover.
     *
     * @param string|null $generatedTurnover
     *
     * @return ProductDIQuote
     */
    public function setGeneratedTurnover($generatedTurnover = null)
    {
        $this->generatedTurnover = $generatedTurnover;

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
     * Set summary.
     *
     * @param string|null $summary
     *
     * @return ProductDIQuote
     */
    public function setSummary($summary = null)
    {
        $this->summary = $summary;

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
     * Set frequency.
     *
     * @param string|null $frequency
     *
     * @return ProductDIQuote
     */
    public function setFrequency($frequency = null)
    {
        $this->frequency = $frequency;

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
     * Set tonnage.
     *
     * @param string|null $tonnage
     *
     * @return ProductDIQuote
     */
    public function setTonnage($tonnage = null)
    {
        $this->tonnage = $tonnage;

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
     * Set kookaburaNumber.
     *
     * @param int|null $kookaburaNumber
     *
     * @return ProductDIQuote
     */
    public function setKookaburaNumber($kookaburaNumber = null)
    {
        $this->kookaburaNumber = $kookaburaNumber;

        return $this;
    }

    /**
     * Add productDIQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine
     *
     * @return ProductDIQuote
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
     * Get userInCharge.
     *
     * @return \Paprec\UserBundle\Entity\User|null
     */
    public function getUserInCharge()
    {
        return $this->userInCharge;
    }

    /**
     * Set userInCharge.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userInCharge
     *
     * @return ProductDIQuote
     */
    public function setUserInCharge(\Paprec\UserBundle\Entity\User $userInCharge = null)
    {
        $this->userInCharge = $userInCharge;

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
     * Set agency.
     *
     * @param \Paprec\CommercialBundle\Entity\Agency|null $agency
     *
     * @return ProductDIQuote
     */
    public function setAgency(\Paprec\CommercialBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

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
     * Set businessLine.
     *
     * @param \Paprec\CommercialBundle\Entity\BusinessLine|null $businessLine
     *
     * @return ProductDIQuote
     */
    public function setBusinessLine(\Paprec\CommercialBundle\Entity\BusinessLine $businessLine = null)
    {
        $this->businessLine = $businessLine;

        return $this;
    }
}
