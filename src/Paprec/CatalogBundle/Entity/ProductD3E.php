<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3EOrder
 *
 * @ORM\Table(name="productD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductD3ERepository")
 */
class ProductD3E
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\NotBlank(groups={"package"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="dimensions", type="string", length=500, nullable=true)
     * @Assert\NotBlank(groups={"package"})
     */
    private $dimensions;


    /**
     * @var string
     * Lien description, URL vers une page de description longue du produit
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var int
     * Le coef de manutention
     * @ORM\Column(name="coefHandling", type="integer", nullable=true)
     * @Assert\NotBlank(groups={"custom"})
     * @Assert\Regex(
     *     pattern="/^\d{1,2}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 99,99 (ou 99.99)"
     * )
     */
    private $coefHandling;

    /**
     * @var int
     * Le coef de relevé de numéro de série
     * @ORM\Column(name="coefSerialNumberStmt", type="integer", nullable=true)
     * @Assert\NotBlank(groups={"custom"})
     * @Assert\Regex(
     *     pattern="/^\d{1,2}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 99,99 (ou 99.99)"
     * )
     */
    private $coefSerialNumberStmt;

    /**
     * @var int
     * Le coef de destruction par broyage
     * @ORM\Column(name="coefDestruction", type="integer", nullable=true)
     * @Assert\NotBlank(groups={"custom"})
     * @Assert\Regex(
     *     pattern="/^\d{1,2}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 99,99 (ou 99.99)"
     * )
     */
    private $coefDestruction;

    /**
     * @var string
     * @ORM\Column(name="position", type="integer")
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * @var text
     * @ORM\Column(name="availablePostalCodeIds", type="text", nullable=true)
     * @Assert\Regex(
     *     pattern="/^(\d{2}(\*|(?:\d{3}))(,\s*)?)+$/",
     *     match=true,
     *     message="Les codes postaux doivent être des nombres séparés par des virgules. (ex: 75*, 92150, 36*)"
     * )
     */
    private $availablePostalCodes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPackage", type="boolean")
     * @Assert\NotBlank()
     */
    private $isPackage;

    /**
     * @var int
     *
     * @ORM\Column(name="packageUnitPrice", type="integer", nullable=true)
     * @Assert\NotBlank(groups={"package"})
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $packageUnitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="subName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"package"})
     */
    private $subName;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\ManyToMany(targetEntity="Paprec\CatalogBundle\Entity\Argument", inversedBy="productD3Es")
     * @ORM\JoinTable(name="productD3EsArguments",
     *      joinColumns={@ORM\JoinColumn(name="productD3EId", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="argumentId", referencedColumnName="id")}
     *     )
     */
    private $arguments;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\Picture", mappedBy="productD3E", cascade={"all"})
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EQuoteLine", mappedBy="productD3E", cascade={"all"})
     */
    private $productD3EQuoteLines;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductD3EOrderLine", mappedBy="productD3E", cascade={"all"})
     */
    private $productD3EOrderLines;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductD3EType", mappedBy="productD3E", cascade={"all"})
     */
    private $productD3ETypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3EOrderLines = new ArrayCollection();
        $this->productD3EQuoteLines = new ArrayCollection();
        $this->arguments = new ArrayCollection();
        $this->productD3ETypes = new ArrayCollection();
        $this->pictures = new ArrayCollection();
    }

    /**
     * GESTION DES PICTURES
     */

    public function getPilotPictures()
    {
        $pilotPictures = array();
        foreach ($this->pictures as $picture) {
            if ($picture->getType() == 'PILOTPICTURE') {
                $pilotPictures[] = $picture;
            }
        }
        return $pilotPictures;
    }

    public function getPictos()
    {
        $pictos = array();
        foreach ($this->pictures as $picture) {
            if ($picture->getType() == 'PICTO') {
                $pictos[] = $picture;
            }
        }
        return $pictos;
    }

    public function getPicturesPictures()
    {
        $pictures = array();
        foreach ($this->pictures as $picture) {
            if ($picture->getType() == 'PICTURE') {
                $pictures[] = $picture;
            }
        }
        return $pictures;
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * Set name.
     *
     * @param string $name
     *
     * @return ProductD3E
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return ProductD3E
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dimensions.
     *
     * @param string|null $dimensions
     *
     * @return ProductD3E
     */
    public function setDimensions($dimensions = null)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions.
     *
     * @return string|null
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set reference.
     *
     * @param string|null $reference
     *
     * @return ProductD3E
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
     * Set coefHandling.
     *
     * @param int|null $coefHandling
     *
     * @return ProductD3E
     */
    public function setCoefHandling($coefHandling = null)
    {
        $this->coefHandling = $coefHandling;

        return $this;
    }

    /**
     * Get coefHandling.
     *
     * @return int|null
     */
    public function getCoefHandling()
    {
        return $this->coefHandling;
    }

    /**
     * Set coefSerialNumberStmt.
     *
     * @param int|null $coefSerialNumberStmt
     *
     * @return ProductD3E
     */
    public function setCoefSerialNumberStmt($coefSerialNumberStmt = null)
    {
        $this->coefSerialNumberStmt = $coefSerialNumberStmt;

        return $this;
    }

    /**
     * Get coefSerialNumberStmt.
     *
     * @return int|null
     */
    public function getCoefSerialNumberStmt()
    {
        return $this->coefSerialNumberStmt;
    }

    /**
     * Set coefDestruction.
     *
     * @param int|null $coefDestruction
     *
     * @return ProductD3E
     */
    public function setCoefDestruction($coefDestruction = null)
    {
        $this->coefDestruction = $coefDestruction;

        return $this;
    }

    /**
     * Get coefDestruction.
     *
     * @return int|null
     */
    public function getCoefDestruction()
    {
        return $this->coefDestruction;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return ProductD3E
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set availablePostalCodes.
     *
     * @param string|null $availablePostalCodes
     *
     * @return ProductD3E
     */
    public function setAvailablePostalCodes($availablePostalCodes = null)
    {
        $this->availablePostalCodes = $availablePostalCodes;

        return $this;
    }

    /**
     * Get availablePostalCodes.
     *
     * @return string|null
     */
    public function getAvailablePostalCodes()
    {
        return $this->availablePostalCodes;
    }

    /**
     * Set isDisplayed.
     *
     * @param bool $isDisplayed
     *
     * @return ProductD3E
     */
    public function setIsDisplayed($isDisplayed)
    {
        $this->isDisplayed = $isDisplayed;

        return $this;
    }

    /**
     * Get isDisplayed.
     *
     * @return bool
     */
    public function getIsDisplayed()
    {
        return $this->isDisplayed;
    }

    /**
     * Set isPackage.
     *
     * @param bool $isPackage
     *
     * @return ProductD3E
     */
    public function setIsPackage($isPackage)
    {
        $this->isPackage = $isPackage;

        return $this;
    }

    /**
     * Get isPackage.
     *
     * @return bool
     */
    public function getIsPackage()
    {
        return $this->isPackage;
    }

    /**
     * Set packageUnitPrice.
     *
     * @param int|null $packageUnitPrice
     *
     * @return ProductD3E
     */
    public function setPackageUnitPrice($packageUnitPrice = null)
    {
        $this->packageUnitPrice = $packageUnitPrice;

        return $this;
    }

    /**
     * Get packageUnitPrice.
     *
     * @return int|null
     */
    public function getPackageUnitPrice()
    {
        return $this->packageUnitPrice;
    }

    /**
     * Set subName.
     *
     * @param string|null $subName
     *
     * @return ProductD3E
     */
    public function setSubName($subName = null)
    {
        $this->subName = $subName;

        return $this;
    }

    /**
     * Get subName.
     *
     * @return string|null
     */
    public function getSubName()
    {
        return $this->subName;
    }

    /**
     * Add argument.
     *
     * @param \Paprec\CatalogBundle\Entity\Argument $argument
     *
     * @return ProductD3E
     */
    public function addArgument(\Paprec\CatalogBundle\Entity\Argument $argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Remove argument.
     *
     * @param \Paprec\CatalogBundle\Entity\Argument $argument
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeArgument(\Paprec\CatalogBundle\Entity\Argument $argument)
    {
        return $this->arguments->removeElement($argument);
    }

    /**
     * Get arguments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Add picture.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picture
     *
     * @return ProductD3E
     */
    public function addPicture(\Paprec\CatalogBundle\Entity\Picture $picture)
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * Remove picture.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picture
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePicture(\Paprec\CatalogBundle\Entity\Picture $picture)
    {
        return $this->pictures->removeElement($picture);
    }

    /**
     * Get pictures.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * Add productD3EQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EQuoteLine $productD3EQuoteLine
     *
     * @return ProductD3E
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
     * Add productD3EOrderLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductD3EOrderLine $productD3EOrderLine
     *
     * @return ProductD3E
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
     * Add productD3EType.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType
     *
     * @return ProductD3E
     */
    public function addProductD3EType(\Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType)
    {
        $this->productD3ETypes[] = $productD3EType;

        return $this;
    }

    /**
     * Remove productD3EType.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3EType(\Paprec\CatalogBundle\Entity\ProductD3EType $productD3EType)
    {
        return $this->productD3ETypes->removeElement($productD3EType);
    }

    /**
     * Get productD3ETypes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3ETypes()
    {
        return $this->productD3ETypes;
    }
}
