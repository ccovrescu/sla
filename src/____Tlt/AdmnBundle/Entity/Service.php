<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Service
 *
 * @ORM\Table(name="services")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\ServiceRepository")
 */
class Service extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="services")
	 * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */	 
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="Property", mappedBy="service")
     */
    public $properties;
	
	/**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="service")
     */
    private $equipments;
	
	/**
  	 * @ORM\OneToMany(targetEntity="ServiceToSystems", mappedBy="system")
 	 */
	private $serviceToSystems;
	
   /**
     * Constructor
     */
    public function __construct()
    {
        $this->service_attributes = new ArrayCollection();
		$this->serviceToSystems = new ArrayCollection();
    }	

    /**
     * Set id
     *
     * @param integer $id
     * @return Service
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
     * @return Service
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
     * Set department
     *
     * @param \Tlt\AdmneBundle\Entity\Department $department
     * @return Product
     */
    public function setDepartment(\Tlt\AdmnBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \Tlt\AdmnBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }
	
	/**
     * Add property
     *
     * @param \Tlt\AdmnBundle\Entity\Property $property
     * @return Service
     */
    public function addProperty(\Tlt\AdmnBundle\Entity\Property $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * Remove property
     *
     * @param \Tlt\AdmnBundle\Entity\Property $property
     */
    public function removeProperty(\Tlt\AdmnBundle\Entity\Property $property)
    {
        $this->properties->removeElement($property);
    }
	
    /**
     * Get properties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProperties()
    {
        return $this->properties;
    }
	
    /**
     * Get serviceToSystems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServiceToSystems()
    {
        return $this->serviceToSystems;
    }
	
	// public function getDepartmentName()
	// {
		// return $this->department->getName();
	// }
	
	public function __toString()
	{
		return $this->getName();
	}
}
