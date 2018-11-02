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
     * @ORM\OneToMany(targetEntity="ServiceAttribute", mappedBy="service_attributes")
     */
    public $service_attributes;
	
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
     * Add service_attribute
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceAttribute $service_attribute
     * @return Department
     */
    public function addServiceAttribute(\Tlt\AdmnBundle\Entity\ServiceAttribute $service_attribute)
    {
        $this->service_attributes[] = $service_attribute;

        return $this;
    }

    /**
     * Remove service_attribute
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceAttribute $service_attribute
     */
    public function removeServiceAttribute(\Tlt\AdmnBundle\Entity\ServiceAttribute $service_attribute)
    {
        $this->service_attributes->removeElement($service_attribute);
    }
	
    /**
     * Get service_attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServiceAttributes()
    {
        return $this->service_attributes;
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
