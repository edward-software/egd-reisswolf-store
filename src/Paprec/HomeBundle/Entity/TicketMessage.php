<?php

namespace Paprec\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TicketMessage
 *
 * @ORM\Table(name="ticketMessages")
 * @ORM\Entity(repositoryClass="Paprec\HomeBundle\Repository\TicketMessageRepository")
 */
class TicketMessage
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
     * @ORM\Column(name="content", type="text")
     * @Assert\NotNull()
     */
    private $content;

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

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\HomeBundle\Entity\Ticket")
     * @ORM\JoinColumn(name="ticketId", referencedColumnName="id", nullable=false)
     */
    private $ticket;


    public function __construct()
    {
        $this->dateCreation = new \DateTime();
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
     * Set content
     *
     * @param string $content
     *
     * @return TicketMessage
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return TicketMessage
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
     * @return TicketMessage
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
     * @return TicketMessage
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
     * @return TicketMessage
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
     * @return TicketMessage
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
     * Set ticket
     *
     * @param \Paprec\HomeBundle\Entity\Ticket $ticket
     *
     * @return TicketMessage
     */
    public function setTicket(\Paprec\HomeBundle\Entity\Ticket $ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \Paprec\HomeBundle\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
