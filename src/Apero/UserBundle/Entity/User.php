<?php

namespace Apero\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Apero\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="GroupeAmis", mappedBy="users")
     */
    private $groupesAmis;

    /**
     * Constructor
     */
    public function __construct()
    {
	   parent::__construct();
       $this->groupesAmis = new ArrayCollection();
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

    public function addGroupeAmis(GroupeAmis $groupeAmis)
    {
        $this->groupesAmis[] = $groupeAmis;

        $groupeAmis->addUser($this);

        return $this;
    }

    public function removeGroupesAmis(GroupeAmis $groupeAmis)
    {
        $this->groupesAmis->removeElement($groupeAmis);

        $groupeAmis->removeUser($this);
    }

    public function getGroupesAmis()
    {
        return $this->groupesAmis;
    }

}
