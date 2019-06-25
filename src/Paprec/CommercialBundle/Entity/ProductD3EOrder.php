<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3EOrder
 *
 * @ORM\Table(name="productD3EOrders")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductD3EOrderRepository")
 */
class ProductD3EOrder
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
     * @ORM\Column(name="invoicing_address", type="text", nullable=true)
     */
    private $invoicing_address;

    /**
     * @var string
     *
     * @ORM\Column(name="invoicing_postalCode", type="string", length=255, nullable=true)
     */
    private $invoicing_postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="invoicing_city", type="text", nullable=true)
     */
    private $invoicing_city;

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
     * @ORM\Column(name="preferredContact", type="string", length=10, nullable=true)
     * @Assert\NotBlank()
     */
    private $preferredContact;

    /******************************
     * PROFESSIONAL
     ******************************/
    /**
     * @var string
     *
     * @ORM\Column(name="siret", type="string", length=15, nullable=true)
     * @Assert\Length(
     *     min = 14,
     *     max = 14,
     *     minMessage="Le numéro SIRET est composé de 14 chiffres",
     *     maxMessage="Le numéro SIRET est composé de 14 chiffres"
     * )
     */
    private $siret;

    /**
     * @var string
     *
     * @ORM\Column(name="tvaStatus", type="string", length=50, nullable=true)
     */
    private $tvaStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="tvaNumber", type="string", length=50, nullable=true)
     */
    private $tvaNumber;


    /**
     * @var string
     *
     * @ORM\Column(name="orderStatus", type="string", length=255)
     */
    private $orderStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="totalAmount", type="integer", nullable=true)
     */
    private $totalAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentMethod", type="string", length=255, nullable=true)
     */
    private $paymentMethod;


    /**
     * @var string
     *
     * @ORM\Column(name="signatoryToken", type="string", length=255, nullable=true)
     */
    private $signatoryToken;

    /**
     *
     * Identifiant de la dernière transaction de signature générée
     *
     * @var string
     *
     * @ORM\Column(name="signatoryTransactionId", type="string", length=255, nullable=true)
     */
    private $signatoryTransactionId;

    /**
     *
     * Identifiant de la signature de la dernière transaction de signature électronique
     *
     * @var string
     *
     * @ORM\Column(name="signatorySignatureId", type="string", length=255, nullable=true)
     */
    private $signatorySignatureId;

    /**
     * Facture associée
     * @var string
     *
     * @ORM\Column(name="associatedInvoice", type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $associatedInvoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="installationDate", type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"delivery"})

     */
    private $installationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="removalDate", type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"delivery"})
     */
    private $removalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="domainType", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"delivery"})
     */
    private $domainType;

    /**
     * @var string
     *
     * @ORM\Column(name="accessConditions", type="text", nullable=true)
     * @Assert\Type(type="string", groups={"delivery"})
     */
    private $accessConditions;


    /** ###########################
     *
     *  RELATIONS
     *
     * ########################### */


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EOrderLine", mappedBy="productD3EOrder", cascade={"all"})
     */
    private $productD3EOrderLines;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\BusinessLine", inversedBy="productD3EOrders")
     * @ORM\JoinColumn(name="businessLineId", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $businessLine;

    /**
     * ProductD3EOrder constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3EOrderLines = new ArrayCollection();
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * Set invoicingAddress.
     *
     * @param string|null $invoicingAddress
     *
     * @return ProductD3EOrder
     */
    public function setInvoicingAddress($invoicingAddress = null)
    {
        $this->invoicing_address = $invoicingAddress;

        return $this;
    }

    /**
     * Get invoicingAddress.
     *
     * @return string|null
     */
    public function getInvoicingAddress()
    {
        return $this->invoicing_address;
    }

    /**
     * Set invoicingPostalCode.
     *
     * @param string|null $invoicingPostalCode
     *
     * @return ProductD3EOrder
     */
    public function setInvoicingPostalCode($invoicingPostalCode = null)
    {
        $this->invoicing_postalCode = $invoicingPostalCode;

        return $this;
    }

    /**
     * Get invoicingPostalCode.
     *
     * @return string|null
     */
    public function getInvoicingPostalCode()
    {
        return $this->invoicing_postalCode;
    }

    /**
     * Set invoicingCity.
     *
     * @param string|null $invoicingCity
     *
     * @return ProductD3EOrder
     */
    public function setInvoicingCity($invoicingCity = null)
    {
        $this->invoicing_city = $invoicingCity;

        return $this;
    }

    /**
     * Get invoicingCity.
     *
     * @return string|null
     */
    public function getInvoicingCity()
    {
        return $this->invoicing_city;
    }

    /**
     * Set headofficeAddress.
     *
     * @param string|null $headofficeAddress
     *
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * @return ProductD3EOrder
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
     * Set phone.
     *
     * @param string $phone
     *
     * @return ProductD3EOrder
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
     * Set preferredContact.
     *
     * @param string|null $preferredContact
     *
     * @return ProductD3EOrder
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
     * Set siret.
     *
     * @param string|null $siret
     *
     * @return ProductD3EOrder
     */
    public function setSiret($siret = null)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret.
     *
     * @return string|null
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set tvaStatus.
     *
     * @param string|null $tvaStatus
     *
     * @return ProductD3EOrder
     */
    public function setTvaStatus($tvaStatus = null)
    {
        $this->tvaStatus = $tvaStatus;

        return $this;
    }

    /**
     * Get tvaStatus.
     *
     * @return string|null
     */
    public function getTvaStatus()
    {
        return $this->tvaStatus;
    }

    /**
     * Set tvaNumber.
     *
     * @param string|null $tvaNumber
     *
     * @return ProductD3EOrder
     */
    public function setTvaNumber($tvaNumber = null)
    {
        $this->tvaNumber = $tvaNumber;

        return $this;
    }

    /**
     * Get tvaNumber.
     *
     * @return string|null
     */
    public function getTvaNumber()
    {
        return $this->tvaNumber;
    }

    /**
     * Set orderStatus.
     *
     * @param string $orderStatus
     *
     * @return ProductD3EOrder
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Get orderStatus.
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Set totalAmount.
     *
     * @param int|null $totalAmount
     *
     * @return ProductD3EOrder
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
     * Set paymentMethod.
     *
     * @param string|null $paymentMethod
     *
     * @return ProductD3EOrder
     */
    public function setPaymentMethod($paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod.
     *
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set signatoryToken.
     *
     * @param string|null $signatoryToken
     *
     * @return ProductD3EOrder
     */
    public function setSignatoryToken($signatoryToken = null)
    {
        $this->signatoryToken = $signatoryToken;

        return $this;
    }

    /**
     * Get signatoryToken.
     *
     * @return string|null
     */
    public function getSignatoryToken()
    {
        return $this->signatoryToken;
    }

    /**
     * Set signatoryTransactionId.
     *
     * @param string|null $signatoryTransactionId
     *
     * @return ProductD3EOrder
     */
    public function setSignatoryTransactionId($signatoryTransactionId = null)
    {
        $this->signatoryTransactionId = $signatoryTransactionId;

        return $this;
    }

    /**
     * Get signatoryTransactionId.
     *
     * @return string|null
     */
    public function getSignatoryTransactionId()
    {
        return $this->signatoryTransactionId;
    }

    /**
     * Set signatorySignatureId.
     *
     * @param string|null $signatorySignatureId
     *
     * @return ProductD3EOrder
     */
    public function setSignatorySignatureId($signatorySignatureId = null)
    {
        $this->signatorySignatureId = $signatorySignatureId;

        return $this;
    }

    /**
     * Get signatorySignatureId.
     *
     * @return string|null
     */
    public function getSignatorySignatureId()
    {
        return $this->signatorySignatureId;
    }

    /**
     * Set associatedInvoice.
     *
     * @param string|null $associatedInvoice
     *
     * @return ProductD3EOrder
     */
    public function setAssociatedInvoice($associatedInvoice = null)
    {
        $this->associatedInvoice = $associatedInvoice;

        return $this;
    }

    /**
     * Get associatedInvoice.
     *
     * @return string|null
     */
    public function getAssociatedInvoice()
    {
        return $this->associatedInvoice;
    }

    /**
     * Set installationDate.
     *
     * @param \DateTime|null $installationDate
     *
     * @return ProductD3EOrder
     */
    public function setInstallationDate($installationDate = null)
    {
        $this->installationDate = $installationDate;

        return $this;
    }

    /**
     * Get installationDate.
     *
     * @return \DateTime|null
     */
    public function getInstallationDate()
    {
        return $this->installationDate;
    }

    /**
     * Set removalDate.
     *
     * @param \DateTime|null $removalDate
     *
     * @return ProductD3EOrder
     */
    public function setRemovalDate($removalDate = null)
    {
        $this->removalDate = $removalDate;

        return $this;
    }

    /**
     * Get removalDate.
     *
     * @return \DateTime|null
     */
    public function getRemovalDate()
    {
        return $this->removalDate;
    }

    /**
     * Set domainType.
     *
     * @param string|null $domainType
     *
     * @return ProductD3EOrder
     */
    public function setDomainType($domainType = null)
    {
        $this->domainType = $domainType;

        return $this;
    }

    /**
     * Get domainType.
     *
     * @return string|null
     */
    public function getDomainType()
    {
        return $this->domainType;
    }

    /**
     * Set accessConditions.
     *
     * @param string|null $accessConditions
     *
     * @return ProductD3EOrder
     */
    public function setAccessConditions($accessConditions = null)
    {
        $this->accessConditions = $accessConditions;

        return $this;
    }

    /**
     * Get accessConditions.
     *
     * @return string|null
     */
    public function getAccessConditions()
    {
        return $this->accessConditions;
    }

    /**
     * Add productD3EOrderLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrderLine $productD3EOrderLine
     *
     * @return ProductD3EOrder
     */
    public function addProductD3EOrderLine(\Paprec\CommercialBundle\Entity\ProductD3EOrderLine $productD3EOrderLine)
    {
        $this->productD3EOrderLines[] = $productD3EOrderLine;

        return $this;
    }

    /**
     * Remove productD3EOrderLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrderLine $productD3EOrderLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EOrderLine(\Paprec\CommercialBundle\Entity\ProductD3EOrderLine $productD3EOrderLine)
    {
        return $this->productD3EOrderLines->removeElement($productD3EOrderLine);
    }

    /**
     * Get productD3EOrderLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3EOrderLines()
    {
        return $this->productD3EOrderLines;
    }

    /**
     * Set businessLine.
     *
     * @param \Paprec\CommercialBundle\Entity\BusinessLine|null $businessLine
     *
     * @return ProductD3EOrder
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
