<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Location
 *
 * @ORM\Table(name="locations")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\LocationRepository")
 */
class Location extends AbstractEntity
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
     * @ORM\Column(name="name", type="string", length=128)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Branch", inversedBy="locations")
	 * @ORM\JoinColumn(name="branch", referencedColumnName="id")
     */	 
    private $branch;
	
	/**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="location")
     */
    private $equipments;

	
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equipments = new ArrayCollection();
    }	
	
    /**
     * Set id
     *
     * @param integer $id
     * @return Location
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @return Location
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
     * Set branch
     *
     * @param \Tlt\AdmneBundle\Entity\Branch $branch
     * @return Location
     */
    public function setBranch(\Tlt\AdmnBundle\Entity\Branch $branch = null)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return \Tlt\AdmnBundle\Entity\branch
     */
    public function getBranch()
    {
        return $this->branch;
    }
	
	
	/**
     * Add equipment
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipment
     * @return Department
     */
    public function addEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment)
    {
        $this->equipments[] = $equipment;

        return $this;
    }

    /**
     * Get equipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipments()
    {
        return $this->equipments;
    }	
	
	// public function getbranchName()
	// {
		// if (null === $this->branch)
			// return null;
		
		// return $this->branch->getName();
	// }
	
	public function __toString()
	{
		return $this->getName();
	}
}