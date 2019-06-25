<?php

namespace Paprec\HomeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ticket
 *
 * @ORM\Table(name="tickets")
 * @ORM\Entity(repositoryClass="Paprec\HomeBundle\Repository\TicketRepository")
 */
class Ticket
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
     * Date de création dans le système (automatique)
     *
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * Date de modification dans le système (automatique)
     *
     * @var \DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * Date de suppression dans le système (automatique)
     *
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=500)
     * @Assert\NotNull()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotNull()
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", length=255)
     * @Assert\NotNull()
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     * @Assert\NotNull()
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="invoiceStatus", type="string", length=255)
     * @Assert\NotNull()
     */
    private $invoiceStatus;

    /**************************************************************************************************
     * SYSTEM USER ASSOCIATION
     */
    /**
     * Dernier utilisateur système créateur de l'objet
     *
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=false)
     */
    private $userCreation;

    /**
     * Dernier utilisateur système modificateur de l'objet
     *
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**************************************************************************************************
     * RELATIONS PROPRIETAIRES
     */
    

    /**************************************************************************************************
     * RELATIONS INVERSES
     */

    /**
     * @ORM\OneToMany(targetEntity="Paprec\HomeBundle\Entity\TicketMessage", mappedBy="ticket")
     */
    private $ticketMessages;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\HomeBundle\Entity\TicketFile", mappedBy="ticket")
     */
    private $ticketFiles;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\HomeBundle\Entity\TicketStatus", mappedBy="ticket")
     */
    private $ticketStatus;


    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->ticketMessages = new ArrayCollection();
        $this->ticketFiles = new ArrayCollection();
        $this->ticketStatus = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Ticket
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
     * Set content
     *
     * @param string $content
     *
     * @return Ticket
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set level
     *
     * @param string $level
     *
     * @return Ticket
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Ticket
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
     * Set invoiceStatus
     *
     * @param string $invoiceStatus
     *
     * @return Ticket
     */
    public function setInvoiceStatus($invoiceStatus)
    {
        $this->invoiceStatus = $invoiceStatus;

        return $this;
    }

    /**
     * Get invoiceStatus
     *
     * @return string
     */
    public function getInvoiceStatus()
    {
        return $this->invoiceStatus;
    }

    /**
     * Get userInCharge
     *
     * @return string
     */
    public function getUserInCharge()
    {
        return $this->userInCharge;
    }

    /**
     * Set userInCharge
     *
     * @param string $userInCharge
     *
     * @return Ticket
     */
    public function setUserInCharge($userInCharge)
    {
        $this->userInCharge = $userInCharge;

        return $this;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Ticket
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
     *
     * @return Ticket
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
     * Set deleted
     *
     * @param \DateTime $deleted
     *
     * @return Ticket
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
     * Set userCreation
     *
     * @param \Paprec\UserBundle\Entity\User $userCreation
     *
     * @return Ticket
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
     * Set userUpdate
     *
     * @param \Paprec\UserBundle\Entity\User $userUpdate
     *
     * @return Ticket
     */
    public function setUserUpdate(\Paprec\UserBundle\Entity\User $userUpdate = null)
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
     * Add ticketMessage
     *
     * @param \Paprec\HomeBundle\Entity\TicketMessage $ticketMessage
     *
     * @return Ticket
     */
    public function addTicketMessage(\Paprec\HomeBundle\Entity\TicketMessage $ticketMessage)
    {
        $this->ticketMessages[] = $ticketMessage;

        return $this;
    }

    /**
     * Remove ticketMessage
     *
     * @param \Paprec\HomeBundle\Entity\TicketMessage $ticketMessage
     */
    public function removeTicketMessage(\Paprec\HomeBundle\Entity\TicketMessage $ticketMessage)
    {
        $this->ticketMessages->removeElement($ticketMessage);
    }

    /**
     * Get ticketMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketMessages()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', NULL));
        $criteria->orderBy(array('id'=> "ASC"));
        return $this->ticketMessages->matching($criteria);
    }

    /**
     * Set ticketFile
     *
     * @param \Paprec\HomeBundle\Entity\TicketFile $ticketFile
     *
     * @return Ticket
     */
    public function setTicketFile(\Paprec\HomeBundle\Entity\TicketFile $ticketFile = null)
    {
        $this->ticketFiles = $ticketFile;

        return $this;
    }

    /**
     * Remove ticketFile
     *
     * @param \Paprec\HomeBundle\Entity\TicketFile $ticketFile
     */
    public function removeTicketFile(\Paprec\HomeBundle\Entity\TicketFile $ticketFile)
    {
        $this->ticketFiles->removeElement($ticketFile);
    }

    /**
     * Get ticketFiles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketFiles()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', NULL));
        $criteria->orderBy(array('id'=> "ASC"));
        return $this->ticketFiles->matching($criteria);
    }

    /**
     * Set ticketStatus
     *
     * @param \Paprec\HomeBundle\Entity\TicketStatus $ticketStatus
     *
     * @return Ticket
     */
    public function setTicketStatus(\Paprec\HomeBundle\Entity\TicketStatus $ticketStatus = null)
    {
        $this->ticketStatus = $ticketFile;

        return $this;
    }

    /**
     * Remove ticketFile
     *
     * @param \Paprec\HomeBundle\Entity\TicketStatus $ticketStatus
     */
    public function removeTicketStatus(\Paprec\HomeBundle\Entity\TicketStatus $ticketStatus)
    {
        $this->ticketStatus->removeElement($ticketStatus);
    }

    /**
     * Get ticketStatus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketStatus()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', NULL));
        $criteria->orderBy(array('id'=> "ASC"));
        return $this->ticketStatus->matching($criteria);
    }


    /**
     * Add ticketFile.
     *
     * @param \Paprec\HomeBundle\Entity\TicketFile $ticketFile
     *
     * @return Ticket
     */
    public function addTicketFile(\Paprec\HomeBundle\Entity\TicketFile $ticketFile)
    {
        $this->ticketFiles[] = $ticketFile;

        return $this;
    }

    /**
     * Add ticketStatus.
     *
     * @param \Paprec\HomeBundle\Entity\TicketStatus $ticketStatus
     *
     * @return Ticket
     */
    public function addTicketStatus(\Paprec\HomeBundle\Entity\TicketStatus $ticketStatus)
    {
        $this->ticketStatus[] = $ticketStatus;

        return $this;
    }
}
