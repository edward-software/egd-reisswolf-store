<?php

namespace Paprec\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketFile
 *
 * @ORM\Table(name="ticketFiles")
 * @ORM\Entity(repositoryClass="Paprec\HomeBundle\Repository\TicketFileRepository")
 */
class TicketFile
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
     * Nom du fichier dans le système de fichier (MD5 d'une chaine de caractère unique)
     *
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, unique=true)
     */
    private $path;

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
     * Set path
     *
     * @param string $path
     *
     * @return TicketFile
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return TicketFile
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
     * @return TicketFile
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
     * @return TicketFile
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
     * @return TicketFile
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
     * @return TicketFile
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
     * @return TicketFile
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
