<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostalCode
 *
 * @ORM\Table(name="postalCodes")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PostalCodeRepository")
 */
class PostalCode
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
     * @var text
     * @ORM\Column(name="codes", type="text", nullable=true)
     * @Assert\Regex(
     *     pattern="/^(\d{2}(\*|(?:\d{3}))(,\s*)?)+$/",
     *     match=true,
     *     message="Les codes postaux doivent être des nombres séparés par des virgules. (ex: 75*, 92150, 36*)"
     * )
     */
    private $codes;
    
    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,2}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 99,99 (ou 99.99)"
     * )
     */
    private $rate;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $division;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set division.
     *
     * @param array|null $division
     *
     * @return PostalCode
     */
    public function setDivision($division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return array|null
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return PostalCode
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }



    /**
     * Set rate.
     *
     * @param int $rate
     *
     * @return PostalCode
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate.
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set codes.
     *
     * @param string|null $codes
     *
     * @return PostalCode
     */
    public function setCodes($codes = null)
    {
        $this->codes = $codes;

        return $this;
    }

    /**
     * Get codes.
     *
     * @return string|null
     */
    public function getCodes()
    {
        return $this->codes;
    }
}
