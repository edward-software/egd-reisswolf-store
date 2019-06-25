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
 * @UniqueEntity("email")
 * @UniqueEntity("username")
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
     * @var string
     */
    protected $plainPassword;


    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email(
     *      message = "L'email '{{ value }}' n'a pas un format valide",
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
     * @var array
     *
     * @ORM\Column(name="divisions", type="simple_array", nullable=true)
     */
    private $divisions;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuote", mappedBy="userInCharge")
     */
    private $productDIQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierQuote", mappedBy="userInCharge")
     */
    private $productChantierQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuote", mappedBy="userInCharge")
     */
    private $productD3EQuotes;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="userInCharge")
     */
    private $quoteRequests;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate", mappedBy="userInCharge")
     */
    private $quoteRequestNonCorporates;

    public function __construct()
    {
        parent::__construct();

        $this->dateCreation = new \DateTime();
        $this->productDIQuotes = new ArrayCollection();
        $this->productChantierQuotes = new ArrayCollection();
        $this->productD3EQuotes = new ArrayCollection();

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

    /**
     * Set divisions.
     *
     * @param array|null $divisions
     *
     * @return User
     */
    public function setDivisions($divisions = null)
    {
        $this->divisions = $divisions;

        return $this;
    }

    /**
     * Get divisions.
     *
     * @return array|null
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    /**
     * Add productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\productDIQuote $productDIQuote
     *
     * @return User
     */
    public function addProductDIQuote(\Paprec\CommercialBundle\Entity\productDIQuote $productDIQuote)
    {
        $this->productDIQuotes[] = $productDIQuote;

        return $this;
    }

    /**
     * Remove productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\productDIQuote $productDIQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDIQuote(\Paprec\CommercialBundle\Entity\productDIQuote $productDIQuote)
    {
        return $this->productDIQuotes->removeElement($productDIQuote);
    }

    /**
     * Get productDIQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDIQuotes()
    {
        return $this->productDIQuotes;
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
     * Add productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return User
     */
    public function addProductChantierQuote(\Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote)
    {
        $this->productChantierQuotes[] = $productChantierQuote;

        return $this;
    }

    /**
     * Remove productChantierQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierQuote(\Paprec\CommercialBundle\Entity\ProductChantierQuote $productChantierQuote)
    {
        return $this->productChantierQuotes->removeElement($productChantierQuote);
    }

    /**
     * Get productChantierQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierQuotes()
    {
        return $this->productChantierQuotes;
    }

    /**
     * Add productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return User
     */
    public function addProductD3EQuote(\Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote)
    {
        $this->productD3EQuotes[] = $productD3EQuote;

        return $this;
    }

    /**
     * Remove productD3EQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EQuote(\Paprec\CommercialBundle\Entity\ProductD3EQuote $productD3EQuote)
    {
        return $this->productD3EQuotes->removeElement($productD3EQuote);
    }

    /**
     * Get productD3EQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3EQuotes()
    {
        return $this->productD3EQuotes;
    }

    /**
     * Add quoteRequestNonCorporate.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate
     *
     * @return User
     */
    public function addQuoteRequestNonCorporate(\Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        $this->quoteRequestNonCorporates[] = $quoteRequestNonCorporate;

        return $this;
    }

    /**
     * Remove quoteRequestNonCorporate.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequestNonCorporate(\Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate $quoteRequestNonCorporate)
    {
        return $this->quoteRequestNonCorporates->removeElement($quoteRequestNonCorporate);
    }

    /**
     * Get quoteRequestNonCorporates.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuoteRequestNonCorporates()
    {
        return $this->quoteRequestNonCorporates;
    }
}
