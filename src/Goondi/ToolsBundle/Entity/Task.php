<?php

namespace Goondi\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Index;
use Paprec\UserBundle\Entity\User;

/**
 * Task
 *
 * @ORM\Table(name="ggs_tasks",indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity()
 */
class Task
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
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=true)
     */
    private $userCreation;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime", nullable=false)
     */
    private $dateCreation;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=false)
     */
    private $dateUpdate;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="isValid", type="boolean")
     */
    private $isValid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateStart", type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEnd", type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="text")
     * @Assert\NotBlank()
     */
    private $command;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     * @Assert\NotBlank()
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="text", nullable=true)
     */
    private $result;

    /**
     * @var string
     *
     * @ORM\Column(name="queue", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $queue;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="ags", type="simple_array", nullable=true)
     */
    private $args;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateToExecute", type="datetime", nullable=true)
     */
    private $dateToExecute;

    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->dateCreation = new \Datetime;
    	$this->dateUpdate = new \Datetime;    
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
     * Set userCreation
     *
     * @param \Paprec\UserBundle\Entity\User  $userCreation
     * @return Directory
     */
    public function setUserCreation(User $userCreation)
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
     * Set userUpdate
     *
     * @param \Paprec\UserBundle\Entity\User  $userUpdate
     * @return Directory
     */
    public function setUserUpdate(User $userUpdate)
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * Get userUpdate
     *
     * @return \Paprec\UserBundle\Entity\User 
     */
    public function getUserUpdate()
    {
        return $this->userUpdate;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return File
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
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return File
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime 
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set isValid
     *
     * @param boolean $isValid
     * @return File
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return boolean 
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     * @return Task
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime 
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     * @return Task
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime 
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set command
     *
     * @param string $command
     * @return Task
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string 
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Task
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
     * Set priority
     *
     * @param integer $priority
     * @return Task
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set result
     *
     * @param string $result
     * @return Task
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return string 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set queue
     *
     * @param string $queue
     * @return Task
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get queue
     *
     * @return string 
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Task
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set args
     *
     * @param array $args
     * @return Task
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Get args
     *
     * @return array 
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set dateToExecute
     *
     * @param \DateTime $dateToExecute
     * @return Task
     */
    public function setDateToExecute($dateToExecute)
    {
        $this->dateToExecute = $dateToExecute;

        return $this;
    }

    /**
     * Get dateToExecute
     *
     * @return \DateTime 
     */
    public function getDateToExecute()
    {
        return $this->dateToExecute;
    }
}
