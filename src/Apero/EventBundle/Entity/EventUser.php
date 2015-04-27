<?php

namespace Apero\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventUser
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Apero\EventBundle\Entity\EventUserRepository")
 */
class EventUser
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
     * @var boolean
     *
     * @ORM\Column(name="invite", type="boolean")
     */
    private $invite;

    /**
     * @var boolean
     *
     * @ORM\Column(name="participant", type="boolean")
     */
    private $participant;

    /**
      * @ORM\ManyToOne(targetEntity="Apero\EventBundle\Entity\Event", inversedBy="eventUsers")
      * @ORM\JoinColumn(nullable=false)
      */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Apero\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    public function __construct()
    {
        $this->invite = false;
        $this->participant = false;
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
     * Set invite
     *
     * @param boolean $invite
     * @return EventUser
     */
    public function setInvite($invite)
    {
        $this->invite = $invite;
    
        return $this;
    }

    /**
     * Get invite
     *
     * @return boolean 
     */
    public function getInvite()
    {
        return $this->invite;
    }

    /**
     * Set participant
     *
     * @param boolean $participant
     * @return EventUser
     */
    public function setParticipant($participant)
    {
        $this->participant = $participant;
    
        return $this;
    }

    /**
     * Get participant
     *
     * @return boolean 
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Set event
     *
     * @param \Apero\EventBundle\Entity\Event $event
     * @return EventUser
     */
    public function setEvent(\Apero\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return \Apero\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param \Apero\UserBundle\Entity\User $user
     * @return EventUser
     */
    public function setUser(\Apero\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Apero\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}