<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Systems
 *
 * @ORM\Table(name="systems")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\SystemRepository")
 */
class System extends AbstractEntity
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
  	 * @ORM\OneToMany(targetEntity="Mapping", mappedBy="system")
 	 */
	private $mappings;
	
	/**
  	 * @ORM\OneToMany(targetEntity="ServiceToSystem", mappedBy="system")
 	 */
	private $serviceToSystems;

	/**
  	 * @ORM\OneToMany(targetEntity="GuaranteedValue", mappedBy="system")
 	 */
	private $guaranteedValues;


	
   /**
     * Constructor
     */
    public function __construct()
    {
		$this->serviceToSystems = new ArrayCollection();
		$this->guaranteedValues = new ArrayCollection();
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
     * @return Systems
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
     * Get mappings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMappings()
    {
        return $this->mappings;
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

    /**
     * Get guaranteedValues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuaranteedValues()
    {
        return $this->guaranteedValues;
    }
}