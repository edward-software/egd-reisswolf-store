<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductDI
 *
 * @ORM\Table(name="productDIs")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductDIRepository")
 */
class ProductDI
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
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     * Le volume du produit
     * @ORM\Column(name="capacity", type="string", length=10)
     * @Assert\NotBlank()
     */
    private $capacity;

    /**
     * @var string
     * L'unité du volume du produit (litre, m²,..)
     * @ORM\Column(name="capacityUnit", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $capacityUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="dimensions", type="string", length=500)
     * @Assert\NotBlank()
     */
    private $dimensions;

    /**
     * @var string
     * Lien description, URL vers une page de description longue du produit
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;

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
     * @ORM\Column(name="isPackage", type="boolean")
     */
    private $isPackage;

    /**
     * @var string
     *
     * @ORM\Column(name="subName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"package"})
     */
    private $subName;


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
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\ManyToMany(targetEntity="Paprec\CatalogBundle\Entity\Argument", inversedBy="productDIs")
     * @ORM\JoinTable(name="productDIs_arguments",
     *      joinColumns={@ORM\JoinColumn(name="productDIId", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="argumentId", referencedColumnName="id")}
     *     )
     */
    private $arguments;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\Picture", mappedBy="productDI", cascade={"all"})
     */
    private $pictures;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductDICategory", mappedBy="productDI", cascade={"all"})
     */
    private $productDICategories;

    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuoteLine", mappedBy="productDI", cascade={"all"})
     */
    private $productDIQuoteLines;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->arguments = new ArrayCollection();
        $this->productDICategories = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->pictures = new ArrayCollection();
    }


    /**
     * ######################################
     * getters et setters des attributs hors doctrine
     * ######################################
     */
    public function getCategories()
    {
        $categories = new ArrayCollection();

        foreach ($this->productDICategories as $productDICategory) {
            $categories[] = $productDICategory->getCategory();
        }

        return $categories;
    }

    // Important
    public function setCategories($categories)
    {
        foreach ($categories as $category) {
            $pC = new ProductDICategory();

            $pC->setProductDI($this);
            $pC->setCategory($category);

            $this->addProductDICategory($pC);
        }

    }


    /**
     * ##########################################
     */

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
     * Set name.
     *
     * @param string $name
     *
     * @return ProductDI
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
     * @param string $description
     *
     * @return ProductDI
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dimensions.
     *
     * @param string $dimensions
     *
     * @return ProductDI
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions.
     *
     * @return string
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
     * @return ProductDI
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
     * Set capacityUnit.
     *
     * @param string $capacityUnit
     *
     * @return ProductDI
     */
    public function setCapacityUnit($capacityUnit)
    {
        $this->capacityUnit = $capacityUnit;

        return $this;
    }

    /**
     * Get capacityUnit.
     *
     * @return string
     */
    public function getCapacityUnit()
    {
        return $this->capacityUnit;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return ProductDI
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
     * @return ProductDI
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
     * @return ProductDI
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
     * Set availablePostalCodes.
     *
     * @param string|null $availablePostalCodes
     *
     * @return ProductDI
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
     * @return ProductDI
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
     * Add productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return ProductDI
     */
    public function addProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        $this->productDICategories[] = $productDICategory;

        return $this;
    }

    /**
     * Remove productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        return $this->productDICategories->removeElement($productDICategory);
    }

    /**
     * Get productDICategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDICategories()
    {
        return $this->productDICategories;
    }

    /**
     * Add argument.
     *
     * @param \Paprec\CatalogBundle\Entity\Argument $argument
     *
     * @return ProductDI
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
     * @return ProductDI
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
     * Add productDIQuoteLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuoteLine $productDIQuoteLine
     *
     * @return ProductDI
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
     * Set capacity.
     *
     * @param string $capacity
     *
     * @return ProductDI
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return string
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set isPackage.
     *
     * @param bool $isPackage
     *
     * @return ProductDI
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
     * @return ProductDI
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
     * @return ProductDI
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
}
