<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Announcer
 *
 * @ORM\Table(name="announcers")
 * @ORM\Entity()
 */
class Announcer extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Branch")
     * @ORM\JoinColumn(name="branch", referencedColumnName="id")
     */
    private $branch;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=128)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=64)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="compartment", type="string", length=128, nullable=true)
     */
    private $compartment;


    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param mixed $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * @param string $compartment
     */
    public function setCompartment($compartment)
    {
        $this->compartment = $compartment;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function __toString()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
}
