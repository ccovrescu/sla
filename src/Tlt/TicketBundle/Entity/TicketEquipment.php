<?php
namespace Tlt\TicketBundle\Entity;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Entity\Owner;
use Tlt\AdmnBundle\Entity\Service;
use Tlt\AdmnBundle\Entity\ZoneLocation;

/**
 * TicketEquipment
 */
class TicketEquipment
{
    /**
     * @var Equipment
     */
    protected $equipment;

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
     * Set equipment
     *
     * @param Equipment $equipment
     * @return TicketEquipment
     */
    public function setEquipment($equipment)
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