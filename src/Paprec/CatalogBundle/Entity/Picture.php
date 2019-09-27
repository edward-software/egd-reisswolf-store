<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="pictures")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PictureRepository")
 */
class Picture
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
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Product", inversedBy="pictures")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\CustomArea", inversedBy="pictures")
     */
    private $customArea;

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
     * Set path.
     *
     * @param string $path
     *
     * @return Picture
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Picture
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set product.
     *
     * @param \Paprec\CatalogBundle\Entity\Product|null $product
     *
     * @return Picture
     */
    public function setProduct(\Paprec\CatalogBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return \Paprec\CatalogBundle\Entity\Product|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set customArea.
     *
     * @param \Paprec\CatalogBundle\Entity\CustomArea|null $customArea
     *
     * @return Picture
     */
    public function setCustomArea(\Paprec\CatalogBundle\Entity\CustomArea $customArea = null)
    {
        $this->customArea = $customArea;

        return $this;
    }

    /**
     * Get customArea.
     *
     * @return \Paprec\CatalogBundle\Entity\CustomArea|null
     */
    public function getCustomArea()
    {
        return $this->customArea;
    }
}
