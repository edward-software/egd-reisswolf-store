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
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductDI", inversedBy="pictures")
     */
    private $productDI;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductChantier", inversedBy="pictures")
     */
    private $productChantier;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", inversedBy="pictures")
     */
    private $productD3E;

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
     * Set productDI.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI|null $productDI
     *
     * @return Picture
     */
    public function setProductDI(\Paprec\CatalogBundle\Entity\ProductDI $productDI = null)
    {
        $this->productDI = $productDI;

        return $this;
    }

    /**
     * Get productDI.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductDI|null
     */
    public function getProductDI()
    {
        return $this->productDI;
    }

    /**
     * Set productChantier.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI|null $productChantier
     *
     * @return Picture
     */
    public function setProductChantier(\Paprec\CatalogBundle\Entity\ProductChantier $productChantier = null)
    {
        $this->productChantier = $productChantier;

        return $this;
    }

    /**
     * Get productChantier.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductChantier|null
     */
    public function getProductChantier()
    {
        return $this->productChantier;
    }

    /**
     * Set productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E|null $productD3E
     *
     * @return Picture
     */
    public function setProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E = null)
    {
        $this->productD3E = $productD3E;

        return $this;
    }

    /**
     * Get productD3E.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductD3E|null
     */
    public function getProductD3E()
    {
        return $this->productD3E;
    }
}
