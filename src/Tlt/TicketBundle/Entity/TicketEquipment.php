<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Entity\Service;
use Tlt\AdmnBundle\Entity\System;
use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Entity\ZoneLocation;
use Tlt\AdmnBundle\Entity\Owner;

/**
 * TicketEquipment
 *
 * @ORM\Table(name="ticket_equipments")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class TicketEquipment
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="TicketCreate", inversedBy="ticketEquipments")
	 * @ORM\JoinColumn(name="ticket_create", referencedColumnName="id")
	 */
	protected $ticketCreate;

	/**
	 * @ORM\OneToOne(targetEntity="\Tlt\AdmnBundle\Entity\Equipment")
	 * @ORM\JoinColumn(name="equipment", referencedColumnName="id")
	 */
	protected $equipment;
	
	/**
	 * @ORM\OneToMany(targetEntity="TicketSystem", mappedBy="ticketEquipment", cascade={"persist"})
	 */
	protected $ticketSystems;

    /**
     * @var Department
     */
    public $department;

    /**
     * @var Service
     */
    public $service;

    /**
     * @var Branch
     */
    public $branch;

    /**
     * @var ZoneLocation
     */
	public $zoneLocation;

    /**
     * @var Owner
     */
    public $owner;


	/**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketSystems = new ArrayCollection();
    }
	
    /**
     * Set id
     *
     * @param integer $id
	 * @return TicketEquipment
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
     * Set ticketCreate
     *
     * @param TicketCreate $ticketCreate
	 * @return TicketEquipment
     */
    public function setTicketCreate($ticketCreate)
    {
        $this->ticketCreate = $ticketCreate;
		
		return $this;
    }

    /**
     * Get ticketCreate
     *
     * @return TicketCreate 
     */
    public function getTicketCreate()
    {
        return $this->ticketCreate;
    }

    /**
     * Set equipment
     *
     * @param Equipment $equipment
	 * @return TicketEquipment
     */
    public function setEquipment(Equipment $equipment)
    {
        $this->equipment = $equipment;
		
		return $this;
    }

    /**
     * Get equipment
     *
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
	
	
	public function getTicketSystems()
	{
		return $this->ticketSystems;
	}
	
	public function addTicketSystems(System $system)
	{
		$ticketSystem = new TicketSystem();
		$ticketSystem->setTicketEquipment( $this );
		$ticketSystem->setSystem( $system );
		
		$this->ticketSystems[] =  $ticketSystem ;
		
		return $this;
			
	}
	
	public function setTicketSystems()
	{
		$this->ticketSystems = new ArrayCollection();
		return $this;
	}

    /**
     * Set department
     *
     * @param Department $department
     * @return TicketEquipment
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * Get department
     *
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Get branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set branch
     * @param Branch $branch
     *
     * @return TicketEquipment
     */
    public function setBranch($branch)
    {
        $this->branch	=	$branch;

        return $this;
    }

    /**
     * Get service
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set service
     * @param Service $service
     *
     * @return TicketEquipment
     */
    public function setService($service)
    {
        $this->service	=	$service;

        return $this;
    }

    /**
     * Get zoneLocation
     *
     * @return ZoneLocation
     */
    public function getZoneLocation()
    {
        return $this->zoneLocation;
    }

    /**
     * Set zoneLocation
     * @param ZoneLocation $zoneLocation
     *
     * @return TicketEquipment
     */
    public function setZoneLocation($zoneLocation)
    {
        $this->zoneLocation	=	$zoneLocation;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set owner
     * @param Owner $owner
     *
     * @return TicketEquipment
     */
    public function setOwner($owner)
    {
        $this->owner	=	$owner;

        return $this;
    }
}