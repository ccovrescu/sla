<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\SystemCategory", inversedBy="systems")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;
	
	/**
  	 * @ORM\OneToMany(targetEntity="Mapping", mappedBy="system")
 	 */
	private $mappings;

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="system")
     */
    private $equipments;

    /**
     * @ORM\OneToMany(targetEntity="ServiceToSystem", mappedBy="system")
     */
	private $serviceToSystems;

	/**
  	 * @ORM\OneToMany(targetEntity="GuaranteedValue", mappedBy="system")
 	 */
	private $guaranteedValues;

    /**
     * @var string
     *
     * @ORM\Column(name="criticality", type="string", length=255)
     */
    private $criticality;

	
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
     * @param \Tlt\AdmnBundle\Entity\Department $department
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

    /**
     * @return string
     */
    public function getCriticality()
    {
        return $this->criticality;
    }

    /**
     * @param string $criticality
     */
    public function setCriticality($criticality)
    {
        $this->criticality = $criticality;
    }
	  /**
     * Set category
     *
     * @param \Tlt\AdmnBundle\Entity\SystemCategory $category
     * @return System
     */
    public function setCategory(\Tlt\AdmnBundle\Entity\SystemCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Tlt\AdmnBundle\Entity\SystemCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * Add mappings
     *
     * @param \Tlt\AdmnBundle\Entity\Mapping $mappings
     * @return System
     */
    public function addMapping(\Tlt\AdmnBundle\Entity\Mapping $mappings)
    {
        $this->mappings[] = $mappings;

        return $this;
    }

    /**
     * Remove mappings
     *
     * @param \Tlt\AdmnBundle\Entity\Mapping $mappings
     */
    public function removeMapping(\Tlt\AdmnBundle\Entity\Mapping $mappings)
    {
        $this->mappings->removeElement($mappings);
    }

    /**
     * Add serviceToSystems
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceToSystem $serviceToSystems
     * @return System
     */
    public function addServiceToSystem(\Tlt\AdmnBundle\Entity\ServiceToSystem $serviceToSystems)
    {
        $this->serviceToSystems[] = $serviceToSystems;

        return $this;
    }

    /**
     * Remove serviceToSystems
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceToSystem $serviceToSystems
     */
    public function removeServiceToSystem(\Tlt\AdmnBundle\Entity\ServiceToSystem $serviceToSystems)
    {
        $this->serviceToSystems->removeElement($serviceToSystems);
    }

    /**
     * Add guaranteedValues
     *
     * @param \Tlt\AdmnBundle\Entity\GuaranteedValue $guaranteedValues
     * @return System
     */
    public function addGuaranteedValue(\Tlt\AdmnBundle\Entity\GuaranteedValue $guaranteedValues)
    {
        $this->guaranteedValues[] = $guaranteedValues;

        return $this;
    }

    /**
     * Remove guaranteedValues
     *
     * @param \Tlt\AdmnBundle\Entity\GuaranteedValue $guaranteedValues
     */
    public function removeGuaranteedValue(\Tlt\AdmnBundle\Entity\GuaranteedValue $guaranteedValues)
    {
        $this->guaranteedValues->removeElement($guaranteedValues);
    }

    /**
     * Add equipments
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipments
     * @return System
     */
    public function addEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipments)
    {
        $this->equipments[] = $equipments;

        return $this;
    }

    /**
     * Remove equipments
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipments
     */
    public function removeEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipments)
    {
        $this->equipments->removeElement($equipments);
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

    public function __toString()
    {
        return $this->getName();
    }
}
