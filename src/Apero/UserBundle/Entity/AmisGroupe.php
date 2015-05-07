<?php

namespace Apero\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AmisGroupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Apero\UserBundle\Entity\AmisGroupeRepository")
 */
class AmisGroupe
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
     * @ORM\Column(name="favoris", type="boolean")
     */
    private $favoris;


    /**
     * @ORM\ManyToOne(targetEntity="Apero\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Apero\UserBundle\Entity\GroupeAmis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupe;

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
     * Set favoris
     *
     * @param boolean $favoris
     * @return AmisGroupe
     */
    public function setFavoris($favoris = false)
    {
        $this->favoris = $favoris;
    
        return $this;
    }

    /**
     * Get favoris
     *
     * @return boolean 
     */
    public function getFavoris()
    {
        return $this->favoris;
    }

    /**
     * Set user
     *
     * @param \Apero\UserBundle\Entity\User $user
     * @return AmisGroupe
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

    /**
     * Set groupe
     *
     * @param \Apero\UserBundle\Entity\GroupeAmis $groupe
     * @return AmisGroupe
     */
    public function setGroupe(\Apero\UserBundle\Entity\GroupeAmis $groupe)
    {
        $this->groupe = $groupe;
    
        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Apero\UserBundle\Entity\GroupeAmis 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
}