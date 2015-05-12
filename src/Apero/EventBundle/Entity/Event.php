<?php

namespace Apero\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Apero\EventBundle\Entity\EventRepository")
 */
class Event
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
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Apero\EventBundle\Entity\Bar")
     */
    private $bar;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     *
     * @ORM\Column(name="genre", type="boolean")
     */
    private $genre;

    /**
     * @ORM\ManyToOne(targetEntity="Apero\UserBundle\Entity\User")
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="Apero\EventBundle\Entity\EventUser", mappedBy="event")
     */
    private $eventUsers;

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
     * Set bar
     *
     * @param string $bar
     * @return Event
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
    
        return $this;
    }

    /**
     * Get bar
     *
     * @return string 
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Event
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set createdBy
     *
     * @param \Apero\UserBundle\Entity\User $createdBy
     * @return Event
     */
    public function setCreatedBy(\Apero\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Apero\UserBundle\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date = new \DateTime();
    }
    
    /**
     * Add eventUsers
     *
     * @param \Apero\EventBundle\Entity\EventUser $eventUsers
     * @return Event
     */
    public function addEventUser(\Apero\EventBundle\Entity\EventUser $eventUsers)
    {
        $this->eventUsers[] = $eventUsers;

        $eventUsers->setEvent($this);
    
        return $this;
    }

    /**
     * Remove eventUsers
     *
     * @param \Apero\EventBundle\Entity\EventUser $eventUsers
     */
    public function removeEventUser(\Apero\EventBundle\Entity\EventUser $eventUsers)
    {
        $this->eventUsers->removeElement($eventUsers);
    }

    /**
     * Get eventUsers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEventUsers()
    {
        return $this->eventUsers;
    }

    /**
     * Set genre
     *
     * @param boolean $genre
     * @return Event
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    
        return $this;
    }

    /**
     * Get genre
     *
     * @return boolean 
     */
    public function getGenre()
    {
        return $this->genre;
    }
}