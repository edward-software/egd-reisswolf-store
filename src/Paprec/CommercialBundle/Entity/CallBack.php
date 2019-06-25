<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * CallBack
 *
 * @ORM\Table(name="callBacks")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\CallBackRepository")
 */
class CallBack
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
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *      message = "The email '{{ value }}' is not a valid email."
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
     * @ORM\Column(name="treatmentStatus", type="string", length=255)
     */
    private $treatmentStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCallBack", type="datetime")
     * @Assert\GreaterThan("today", message="La date doit être supérieure à la date du jour")
     * @Assert\NotBlank()
     */
    private $dateCallBack;

    /**
     * @var string
     *
     * @ORM\Column(name="timeCallBack", type="string", length=255, nullable=true)
     * @Assert\Time
     * @Assert\NotBlank()
     */
    private $timeCallBack;

    /**
     * @var array|null
     *
     * @ORM\Column(name="cartContent", type="json", nullable=true)
     */
    private $cartContent;

    /**
     * CallBack constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->content = array();
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * @return CallBack
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
     * Set function.
     *
     * @param string|null $function
     *
     * @return CallBack
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
     * Set treatmentStatus.
     *
     * @param string $treatmentStatus
     *
     * @return CallBack
     */
    public function setTreatmentStatus($treatmentStatus)
    {
        $this->treatmentStatus = $treatmentStatus;

        return $this;
    }

    /**
     * Get treatmentStatus.
     *
     * @return string
     */
    public function getTreatmentStatus()
    {
        return $this->treatmentStatus;
    }

    /**
     * Set dateCallBack.
     *
     * @param \DateTime $dateCallBack
     *
     * @return CallBack
     */
    public function setDateCallBack($dateCallBack)
    {
        $this->dateCallBack = $dateCallBack;

        return $this;
    }

    /**
     * Get dateCallBack.
     *
     * @return \DateTime
     */
    public function getDateCallBack()
    {
        return $this->dateCallBack;
    }


    /**
     * @param null $cartContent
     * @return $this
     */
    public function setCartContent($cartContent = null)
    {
        $this->cartContent = $cartContent;

        return $this;
    }


    /**
     * @return array|null
     */
    public function getCartContent()
    {
        return $this->cartContent;
    }

    /**
     * Set timeCallBack.
     *
     * @param string|null $timeCallBack
     *
     * @return CallBack
     */
    public function setTimeCallBack($timeCallBack = null)
    {
        $this->timeCallBack = $timeCallBack;

        return $this;
    }

    /**
     * Get timeCallBack.
     *
     * @return string|null
     */
    public function getTimeCallBack()
    {
        return $this->timeCallBack;
    }
}
