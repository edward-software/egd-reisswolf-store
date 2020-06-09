<?php

namespace Paprec\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Paprec\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, repositoryMethod="isMailUnique")
 * @UniqueEntity(fields={"username"}, repositoryMethod="isUsernameUnique")
 * @UniqueEntity(fields={"usernameCanonical"}, repositoryMethod="isUsernameCanonicalUnique")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @Assert\NotBlank(groups={"password"})
     * @var string
     */
    protected $plainPassword;


    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email(
     *      message = "email_error",
     *      checkMX = true
     * )
     */
    protected $email;

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
     * @ORM\Column(name="companyName", type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=255, nullable=true)
     */
    private $lang;


    /**
     * #################################
     *              Relations
     * #################################
     */


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\PostalCode", mappedBy="userInCharge")
     */
    private $postalCodes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="userInCharge")
     */
    private $quoteRequests;


    public function __construct()
    {
        parent::__construct();

        $this->dateCreation = new \DateTime();
        $this->products = new ArrayCollection();
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
     * Set companyName.
     *
     * @param string|null $companyName
     *
     * @return User
     */
    public function setCompanyName($companyName = null)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set lastName.
     *
     * @param string|null $lastName
     *
     * @return User
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
     * @return User
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
     * Fonction manuelle pour afficher Prénom + Nom dans un tableau Goondi
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->getLastName();
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return User
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
     * @return User
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
     * @return User
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

//    /**
//     * Set postalCodes.
//     *
//     * @param string $postalCodes
//     *
//     * @return User
//     */
//    public function setPostalCodes($postalCodes)
//    {
//        $this->postalCodes = $postalCodes;
//
//        return $this;
//    }
//
//    /**
//     * Get postalCodes.
//     *
//     * @return string
//     */
//    public function getPostalCodes()
//    {
//        return $this->postalCodes;
//    }

    /**
     * Add product.
     *
     * @param \Paprec\CatalogBundle\Entity\Product $product
     *
     * @return User
     */
    public function addProduct(\Paprec\CatalogBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product.
     *
     * @param \Paprec\CatalogBundle\Entity\product $product
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProduct(\Paprec\CatalogBundle\Entity\product $product)
    {
        return $this->products->removeElement($product);
    }

    /**
     * Get products.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add postalCode.
     *
     * @param \Paprec\CatalogBundle\Entity\PostalCode $postalCode
     *
     * @return User
     */
    public function addPostalCode(\Paprec\CatalogBundle\Entity\PostalCode $postalCode)
    {
        $this->postalCodes[] = $postalCode;

        return $this;
    }

    /**
     * Remove postalCode.
     *
     * @param \Paprec\CatalogBundle\Entity\PostalCode $postalCode
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePostalCode(\Paprec\CatalogBundle\Entity\PostalCode $postalCode)
    {
        return $this->postalCodes->removeElement($postalCode);
    }

    /**
     * Get postalCodes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPostalCodes()
    {
        return $this->postalCodes;
    }

    /**
     * Add quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return User
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
     * Set lang.
     *
     * @param string|null $lang
     *
     * @return User
     */
    public function setLang($lang = null)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string|null
     */
    public function getLang()
    {
        return $this->lang;
    }
}
