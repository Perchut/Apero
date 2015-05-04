<?php

namespace Apero\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupeAmis
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Apero\UserBundle\Entity\GroupeAmisRepository")
 */
class GroupeAmis
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="groupesAmis")
     * @ORM\JoinTable(name="users_amis")
     */
    private $users;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    public function getUsers()
    {
        return $this->users;
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
     * Set name
     *
     * @param string $name
     * @return GroupeAmis
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
}
