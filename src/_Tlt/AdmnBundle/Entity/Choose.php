<?php

namespace Tlt\AdmnBundle\Entity;

/**
 * Choose
 */
class Choose
{
    /**
     * @var integer
     */
    private $owner;

    /**
     * @var integer
     */
    private $branch;

    /**
     * @var integer
     */
    private $location;

    /**
     * @var integer
     */
    private $department;

    /**
     * @var integer
     */
    private $service;

    /**
     * @var integer
     */
    private $equipment;

	
    /**
     * Set owner
     *
     * @param integer $owner
     * @return Choose
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }
	
    /**
     * Get owner
     *
     * @return integer 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set branch
     *
     * @param integer $branch
     * @return Choose
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;

        return $this;
    }
	
    /**
     * Get branch
     *
     * @return integer 
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set location
     *
     * @param integer $location
     * @return Choose
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }
	
    /**
     * Get location
     *
     * @return integer 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set department
     *
     * @param integer $department
     * @return Choose
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }
	
    /**
     * Get department
     *
     * @return integer 
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set service
     *
     * @param integer $service
     * @return Choose
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }
	
    /**
     * Get service
     *
     * @return integer 
     */
    public function getService()
    {
        return $this->service;
    }
	
   /**
     * Set equipment
     *
     * @param integer $equipment
     * @return Choose
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;

        return $this;
    }
	
    /**
     * Get equipment
     *
     * @return integer 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
}