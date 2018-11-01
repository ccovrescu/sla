<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneLocation
 *
 * @ORM\Table(name="zones_locations")
 * @ORM\Entity()
 */
class ZoneLocation extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Branch", inversedBy="zoneLocations")
	 * @ORM\JoinColumn(name="branch_id", referencedColumnName="id")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $branch;
	
    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="zoneLocations")
	 * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */	 
    private $location;
	
	/**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="zoneLocation")
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
     * @return ZoneLocation
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
     * Set branch
     *
     * @param \Tlt\AdmneBundle\Entity\Branch $branch
     * @return ZoneLocation
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
     * Set location
     *
     * @param \Tlt\AdmneBundle\Entity\Location $location
     * @return ZoneLocation
     */
    public function setLocation(\Tlt\AdmnBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \Tlt\AdmnBundle\Entity\location
     */
    public function getLocation()
    {
        return $this->location;
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
	
	public function getName()
	{
		// return $this->branch->getName() . ' - ' . $this->location->getName();
		return $this->location->getName();
	}
	
	public function __toString()
	{
		// return $this->branch->getName() . ' - ' . $this->location->getName();
		return $this->location->getName();
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
}
