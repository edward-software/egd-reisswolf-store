<?php

namespace Goondi\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Index;
use Paprec\UserBundle\Entity\User;

/**
 * UpdateTask
 *
 * @ORM\Table(name="ggs_updateTasks",indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity()
 */
class UpdateTask
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=false)
     */
    private $userCreation;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userValidationId", referencedColumnName="id", nullable=true)
     */
    private $userValidation;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime", nullable=false)
     */
    private $dateCreation;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateValidation", type="datetime", nullable=true)
     */
    private $dateValidation;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=255)
     */
    private $object;

    /**
     * @var integer
     *
     * @ORM\Column(name="objectId", type="integer")
     */
    private $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="currentValue", type="text", nullable=true)
     */
    private $currentValue;

    /**
     * @var string
     *
     * @ORM\Column(name="newValue", type="text", nullable=true)
     */
    private $newValue;

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
    	$this->dateCreation = new \Datetime;
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return UpdateTask
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateValidation
     *
     * @param \DateTime $dateValidation
     * @return UpdateTask
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation
     *
     * @return \DateTime 
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return UpdateTask
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return UpdateTask
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set object
     *
     * @param string $object
     * @return UpdateTask
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return string 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return UpdateTask
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set currentValue
     *
     * @param string $currentValue
     * @return UpdateTask
     */
    public function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;

        return $this;
    }

    /**
     * Get currentValue
     *
     * @return string 
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set newValue
     *
     * @param string $newValue
     * @return UpdateTask
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * Get newValue
     *
     * @return string 
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Set userCreation
     *
     * @param \Paprec\UserBundle\Entity\User $userCreation
     * @return UpdateTask
     */
    public function setUserCreation(\Paprec\UserBundle\Entity\User $userCreation)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation
     *
     * @return \Paprec\UserBundle\Entity\User 
     */
    public function getUserCreation()
    {
        return $this->userCreation;
    }

    /**
     * Set userValidation
     *
     * @param \Paprec\UserBundle\Entity\User $userValidation
     * @return UpdateTask
     */
    public function setUserValidation(\Paprec\UserBundle\Entity\User $userValidation = null)
    {
        $this->userValidation = $userValidation;

        return $this;
    }

    /**
     * Get userValidation
     *
     * @return \Paprec\UserBundle\Entity\User 
     */
    public function getUserValidation()
    {
        return $this->userValidation;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return UpdateTask
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
}
