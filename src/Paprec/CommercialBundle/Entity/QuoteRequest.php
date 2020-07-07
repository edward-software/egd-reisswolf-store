<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * QuoteRequest
 *
 * @ORM\Table(name="quoteRequests")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\QuoteRequestRepository")
 * @UniqueEntity("number")
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
     * #################################
     *              SYSTEM USER ASSOCIATION
     * #################################
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=true)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\Length(max=500, payload="tokenStringLengthNotValid")
     * @Assert\NotBlank(payload="tokenNotValid")
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=255)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="canton", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $canton;

    /**
     * @var string
     *
     * @ORM\Column(name="businessName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $businessName;

    /**
     * @var string
     *
     * @ORM\Column(name="civility", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $civility;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     * @Assert\Email(
     *     groups={"public"},
     *      message = "email_error"
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isMultisite", type="boolean")
     * @Assert\NotBlank(groups={"public"})
     */
    private $isMultisite;

    /**
     * @var string
     *
     * @ORM\Column(name="staff", type="text")
     * @Assert\NotBlank(groups={"public"})
     */
    private $staff;

    /**
     * @var string
     *
     * @ORM\Column(name="access", type="text")
     * @Assert\NotBlank(groups={"public"})
     */
    private $access;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $city;

    /**
     * "Commentaire client" rempli par l'utilisateur Front Office
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

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
     * @var int
     *
     * @ORM\Column(name="overallDiscount", type="integer")
     */
    private $overallDiscount;

    /**
     * "Commentaire client" rempli par le commercial dans le back-office
     * @var string
     *
     * @ORM\Column(name="salesmanComment", type="text", nullable=true)
     */
    private $salesmanComment;

    /**
     * @var int
     *
     * @ORM\Column(name="annualBudget", type="integer", nullable=true)
     */
    private $annualBudget;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=255, nullable=true)
     */
    private $frequency;

    /**
     * @var string
     *
     * @ORM\Column(name="frequencyTimes", type="string", length=255, nullable=true)
     */
    private $frequencyTimes;

    /**
     * @var string
     *
     * @ORM\Column(name="frequencyInterval", type="string", length=255, nullable=true)
     */
    private $frequencyInterval;

    /**
     * @var string
     *
     * @ORM\Column(name="customerId", type="string", length=255, nullable=true)
     */
    private $customerId;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryLastName1", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory"})
     */
    private $signatoryLastName1;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryFirstName1", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory"})
     */
    private $signatoryFirstName1;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryTitle1", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory"})
     */
    private $signatoryTitle1;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryLastName2", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory2"})
     */
    private $signatoryLastName2;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryFirstName2", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory2"})
     */
    private $signatoryFirstName2;

    /**
     * @var string
     *
     * @ORM\Column(name="signatoryTitle2", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"signatory2"})
     */
    private $signatoryTitle2;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isSingleSignatory", type="boolean", nullable=true)
     * @Assert\NotBlank(groups={"signatory"})
     */
    private $isSingleSignatory;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="quoteRequests")
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\PostalCode", inversedBy="quoteRequests")
     * @Assert\NotBlank(groups={"public"})
     */
    private $postalCode;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequestLine", mappedBy="quoteRequest")
     */
    private $quoteRequestLines;

    /**
     * @ORM\ManyToMany(targetEntity="Paprec\CatalogBundle\Entity\OtherNeed", mappedBy="quoteRequests")
     */
    private $otherNeeds;

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
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->quoteRequestLines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->otherNeeds = new \Doctrine\Common\Collections\ArrayCollection();
        $this->overallDiscount = 0;
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
     * Set canton.
     *
     * @param string|null $canton
     *
     * @return QuoteRequest
     */
    public function setCanton($canton = null)
    {
        $this->canton = $canton;

        return $this;
    }

    /**
     * Get canton.
     *
     * @return string|null
     */
    public function getCanton()
    {
        return $this->canton;
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
     * Set isMultisite.
     *
     * @param bool $isMultisite
     *
     * @return QuoteRequest
     */
    public function setIsMultisite($isMultisite)
    {
        $this->isMultisite = $isMultisite;

        return $this;
    }

    /**
     * Get isMultisite.
     *
     * @return bool
     */
    public function getIsMultisite()
    {
        return $this->isMultisite;
    }

    /**
     * Set address.
     *
     * @param string|null $address
     *
     * @return QuoteRequest
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

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
     * Set city.
     *
     * @param string|null $city
     *
     * @return QuoteRequest
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
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return QuoteRequest
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
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
     * Set overallDiscount.
     *
     * @param int|null $overallDiscount
     *
     * @return QuoteRequest
     */
    public function setOverallDiscount($overallDiscount = null)
    {
        $this->overallDiscount = $overallDiscount;

        return $this;
    }

    /**
     * Get overallDiscount.
     *
     * @return int|null
     */
    public function getOverallDiscount()
    {
        return $this->overallDiscount;
    }

    /**
     * Set salesmanComment.
     *
     * @param string|null $salesmanComment
     *
     * @return QuoteRequest
     */
    public function setSalesmanComment($salesmanComment = null)
    {
        $this->salesmanComment = $salesmanComment;

        return $this;
    }

    /**
     * Get salesmanComment.
     *
     * @return string|null
     */
    public function getSalesmanComment()
    {
        return $this->salesmanComment;
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
     * Set userCreation.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userCreation
     *
     * @return QuoteRequest
     */
    public function setUserCreation(\Paprec\UserBundle\Entity\User $userCreation = null)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation.
     *
     * @return \Paprec\UserBundle\Entity\User|null
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
     * @return QuoteRequest
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
     * Add quoteRequestLine.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestLine $quoteRequestLine
     *
     * @return QuoteRequest
     */
    public function addQuoteRequestLine(\Paprec\CommercialBundle\Entity\QuoteRequestLine $quoteRequestLine)
    {
        $this->quoteRequestLines[] = $quoteRequestLine;

        return $this;
    }

    /**
     * Remove quoteRequestLine.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestLine $quoteRequestLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequestLine(\Paprec\CommercialBundle\Entity\QuoteRequestLine $quoteRequestLine)
    {
        return $this->quoteRequestLines->removeElement($quoteRequestLine);
    }

    /**
     * Get quoteRequestLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuoteRequestLines()
    {
        return $this->quoteRequestLines;
    }

    /**
     * Set totalAmount.
     *
     * @param int|null $totalAmount
     *
     * @return QuoteRequest
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
     * Set frequencyTimes.
     *
     * @param string|null $frequencyTimes
     *
     * @return QuoteRequest
     */
    public function setFrequencyTimes($frequencyTimes = null)
    {
        $this->frequencyTimes = $frequencyTimes;

        return $this;
    }

    /**
     * Get frequencyTimes.
     *
     * @return string|null
     */
    public function getFrequencyTimes()
    {
        return $this->frequencyTimes;
    }

    /**
     * Set frequencyInterval.
     *
     * @param string|null $frequencyInterval
     *
     * @return QuoteRequest
     */
    public function setFrequencyInterval($frequencyInterval = null)
    {
        $this->frequencyInterval = $frequencyInterval;

        return $this;
    }

    /**
     * Get frequencyInterval.
     *
     * @return string|null
     */
    public function getFrequencyInterval()
    {
        return $this->frequencyInterval;
    }

    /**
     * Set locale.
     *
     * @param string|null $locale
     *
     * @return QuoteRequest
     */
    public function setLocale($locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set staff.
     *
     * @param string $staff
     *
     * @return QuoteRequest
     */
    public function setStaff($staff)
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * Get staff.
     *
     * @return string
     */
    public function getStaff()
    {
        return $this->staff;
    }

    /**
     * Set access.
     *
     * @param string $access
     *
     * @return QuoteRequest
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access.
     *
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set postalCode.
     *
     * @param \Paprec\CatalogBundle\Entity\PostalCode|null $postalCode
     *
     * @return QuoteRequest
     */
    public function setPostalCode(\Paprec\CatalogBundle\Entity\PostalCode $postalCode = null)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return \Paprec\CatalogBundle\Entity\PostalCode|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set number.
     *
     * @param string|null $number
     *
     * @return QuoteRequest
     */
    public function setNumber($number = null)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set customerId.
     *
     * @param string|null $customerId
     *
     * @return QuoteRequest
     */
    public function setCustomerId($customerId = null)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId.
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set annualBudget.
     *
     * @param int|null $annualBudget
     *
     * @return QuoteRequest
     */
    public function setAnnualBudget($annualBudget = null)
    {
        $this->annualBudget = $annualBudget;

        return $this;
    }

    /**
     * Get annualBudget.
     *
     * @return int|null
     */
    public function getAnnualBudget()
    {
        return $this->annualBudget;
    }

    /**
     * Set reference.
     *
     * @param string|null $reference
     *
     * @return QuoteRequest
     */
    public function setReference($reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference.
     *
     * @return string|null
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set signatoryLastName1.
     *
     * @param string|null $signatoryLastName1
     *
     * @return QuoteRequest
     */
    public function setSignatoryLastName1($signatoryLastName1 = null)
    {
        $this->signatoryLastName1 = $signatoryLastName1;

        return $this;
    }

    /**
     * Get signatoryLastName1.
     *
     * @return string|null
     */
    public function getSignatoryLastName1()
    {
        return $this->signatoryLastName1;
    }

    /**
     * Set signatoryFirstName1.
     *
     * @param string|null $signatoryFirstName1
     *
     * @return QuoteRequest
     */
    public function setSignatoryFirstName1($signatoryFirstName1 = null)
    {
        $this->signatoryFirstName1 = $signatoryFirstName1;

        return $this;
    }

    /**
     * Get signatoryFirstName1.
     *
     * @return string|null
     */
    public function getSignatoryFirstName1()
    {
        return $this->signatoryFirstName1;
    }

    /**
     * Set signatoryTitle1.
     *
     * @param string|null $signatoryTitle1
     *
     * @return QuoteRequest
     */
    public function setSignatoryTitle1($signatoryTitle1 = null)
    {
        $this->signatoryTitle1 = $signatoryTitle1;

        return $this;
    }

    /**
     * Get signatoryTitle1.
     *
     * @return string|null
     */
    public function getSignatoryTitle1()
    {
        return $this->signatoryTitle1;
    }

    /**
     * Set signatoryLastName2.
     *
     * @param string|null $signatoryLastName2
     *
     * @return QuoteRequest
     */
    public function setSignatoryLastName2($signatoryLastName2 = null)
    {
        $this->signatoryLastName2 = $signatoryLastName2;

        return $this;
    }

    /**
     * Get signatoryLastName2.
     *
     * @return string|null
     */
    public function getSignatoryLastName2()
    {
        return $this->signatoryLastName2;
    }

    /**
     * Set signatoryFirstName2.
     *
     * @param string|null $signatoryFirstName2
     *
     * @return QuoteRequest
     */
    public function setSignatoryFirstName2($signatoryFirstName2 = null)
    {
        $this->signatoryFirstName2 = $signatoryFirstName2;

        return $this;
    }

    /**
     * Get signatoryFirstName2.
     *
     * @return string|null
     */
    public function getSignatoryFirstName2()
    {
        return $this->signatoryFirstName2;
    }

    /**
     * Set signatoryTitle2.
     *
     * @param string|null $signatoryTitle2
     *
     * @return QuoteRequest
     */
    public function setSignatoryTitle2($signatoryTitle2 = null)
    {
        $this->signatoryTitle2 = $signatoryTitle2;

        return $this;
    }

    /**
     * Get signatoryTitle2.
     *
     * @return string|null
     */
    public function getSignatoryTitle2()
    {
        return $this->signatoryTitle2;
    }

    /**
     * Set isSingleSignatory.
     *
     * @param bool $isSingleSignatory
     *
     * @return QuoteRequest
     */
    public function setIsSingleSignatory($isSingleSignatory)
    {
        $this->isSingleSignatory = $isSingleSignatory;

        return $this;
    }

    /**
     * Get isSingleSignatory.
     *
     * @return bool
     */
    public function getIsSingleSignatory()
    {
        return $this->isSingleSignatory;
    }

    /**
     * Retoune true si le signataires sont correctement définis, false sinon
     *
     * @return bool
     */
    public function hasValidSignatories()
    {
        if ($this->isSingleSignatory) {
            return ($this->signatoryLastName1
                && $this->signatoryFirstName1
                && $this->signatoryTitle1);
        }

        return ($this->signatoryLastName1
            && $this->signatoryFirstName1
            && $this->signatoryTitle1
            && $this->signatoryLastName2
            && $this->signatoryFirstName2
            && $this->signatoryTitle2);
    }

    /**
     * Add otherNeed.
     *
     * @param \Paprec\CatalogBundle\Entity\OtherNeed $otherNeed
     *
     * @return QuoteRequest
     */
    public function addOtherNeed(\Paprec\CatalogBundle\Entity\OtherNeed $otherNeed)
    {
        $this->otherNeeds[] = $otherNeed;

        return $this;
    }

    /**
     * Remove otherNeed.
     *
     * @param \Paprec\CatalogBundle\Entity\OtherNeed $otherNeed
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOtherNeed(\Paprec\CatalogBundle\Entity\OtherNeed $otherNeed)
    {
        return $this->otherNeeds->removeElement($otherNeed);
    }

    /**
     * Get otherNeeds.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOtherNeeds()
    {
        return $this->otherNeeds;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return QuoteRequest
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
