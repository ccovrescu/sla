<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\OneToMany(targetEntity="ZoneLocation", mappedBy="location")
     */
    private $zoneLocations;	
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equipments = new ArrayCollection();
		$this->zoneLocations = new ArrayCollection();
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

	public function __toString()
	{
		return $this->getName();
	}

    /**
     * Add zoneLocations
     *
     * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocations
     * @return Location
     */
    public function addZoneLocation(\Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocations)
    {
        $this->zoneLocations[] = $zoneLocations;

        return $this;
    }

    /**
     * Remove zoneLocations
     *
     * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocations
     */
    public function removeZoneLocation(\Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocations)
    {
        $this->zoneLocations->removeElement($zoneLocations);
    }

    /**
     * Get zoneLocations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getZoneLocations()
    {
        return $this->zoneLocations;
    }
}
