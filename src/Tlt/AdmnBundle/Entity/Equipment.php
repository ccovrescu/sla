<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Equipment
 *
 * @ORM\Table(name="equipments")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\EquipmentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Equipment extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Owner", inversedBy="equipments")
	 * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */	 
    private $owner;
	
    /**
     * @ORM\ManyToOne(targetEntity="ZoneLocation", inversedBy="equipments")
	 * @ORM\JoinColumn(name="zoneLocation", referencedColumnName="id")
     */	 
    private $zoneLocation;
	
    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="equipments")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */	 
    private $service;
	
	/**
	 * @var float
     *
     * @ORM\Column(name="total", type="decimal", precision=7, scale=2)
	 */
	private $total;
	
	/**
	 * @var boolean
     *
     * @ORM\Column(name="in_pam", type="boolean")
	 */
	private $inPam;
	
	/**
	 * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive = true;

    /**
     * @ORM\OneToMany(targetEntity="PropertyValue", mappedBy="equipment")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $propertiesValues;

	protected $branch;


    public function __construct()
    {
        $this->propertiesValues = new ArrayCollection();
    }	

    /**
     * Set id
     *
     * @param integer $id
     * @return Equipment
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
     * @return Equipment
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
     * Set owner
     *
     * @param \Tlt\AdmnBundle\Entity\Owner $owner
     * @return Equipment
     */
    public function setOwner(\Tlt\AdmnBundle\Entity\Owner $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Tlt\AdmnBundle\Entity\Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set zoneLocation
     *
     * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation
     * @return Equipment
     */
    public function setZoneLocation(\Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation = null)
    {
        $this->zoneLocation = $zoneLocation;

        return $this;
    }

    /**
     * Get zoneLocation
     *
     * @return \Tlt\AdmnBundle\Entity\ZoneLocation
     */
    public function getZoneLocation()
    {
        return $this->zoneLocation;
    }

    /**
     * Set service
     *
     * @param \Tlt\AdmnBundle\Entity\Service $service
     * @return Equipment
     */
    public function setService(\Tlt\AdmnBundle\Entity\Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \Tlt\AdmnBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
    }
	
    /**
     * Set total
     *
     * @param integer $total
     * @return Equipment
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set inPam
     *
     * @param boolean $inPam
     * @return Equipment
     */
    public function setInPam($inPam)
    {
        $this->inPam = $inPam;

        return $this;
    }
	
    /**
     * Get inPam
     *
     * @return boolean 
     */
    public function getInPam()
    {
        return $this->inPam;
    }
	
    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Equipment
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

	public function getUniqueName()
	{
		$uniqueName = $this->name;
		foreach($this->propertiesValues as $propertyValue)
		{
			$uniqueName .= ' | ' . $propertyValue->getValue();
		}
		
		return $uniqueName;
	}
	
	public function __toString()
	{
//        $name = $this->name;
//        foreach ($this->getPropertiesValues() as $property)
//        {
//            $name .= '|' . $property->getValue();
//        }
//		return $name;
        return $this->name;
	}
	
	public function setBranch(\Tlt\AdmnBundle\Entity\Branch $branch)
	{
		$this->branch = $branch;
	}
	
	public function getBranch()
	{
		return $this->branch;
	}
	
	/**
     * Add propertyValue
     *
     * @param \Tlt\AdmnBundle\Entity\PropertyValue $propertyValue
     * @return Equipment
     */
    public function addPropertiesValues(\Tlt\AdmnBundle\Entity\PropertyValue $propertyValue)
    {
        $this->propertiesValues[] = $propertyValue;

        return $this;
    }

    /**
     * Remove propertyValue
     *
     * @param \Tlt\AdmnBundle\Entity\PropertyValue $propertyValue
     */
    public function removeServiceAttributeValue(\Tlt\AdmnBundle\Entity\PropertyValue $propertyValue)
    {
        $this->propertiesValues->removeElement($propertyValue);
    }

    /**
     * Get propertiesValues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPropertiesValues()
    {
        return $this->propertiesValues;
    }
}
