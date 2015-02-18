<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Filter
{
	/**
     * @var \Tlt\AdmnBundle\Entity\Branch
	 */
	private $branch;
	
	/**
     * @var \Tlt\AdmnBundle\Entity\ZoneLocation
	 */
	 private $zoneLocation;
	
	/**
     * @var \Tlt\AdmnBundle\Entity\Department
	 */
	private $department;
	
	/**
     * @var \Tlt\AdmnBundle\Entity\Service
	 */
	 private $service;
	 
	/**
     * @var \Tlt\AdmnBundle\Entity\owner
	 */
	 private $owner;	 
	
    /**
     * Get branch
     *
     * @return \Tlt\AdmnBundle\Entity\Branch 
     */
	public function getBranch()
	{
		return $this->branch;
	}
	
    /**
     * Set branch
	 * @param \Tlt\AdmnBundle\Entity\Branch $branch
     *
     * @return Filter
     */	
	public function setBranch($branch)
	{
		$this->branch	=	$branch;
		
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
     * Set zoneLocation
	 * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation
     *
     * @return Filter
     */
	public function setZoneLocation($zoneLocation)
	{
		$this->zoneLocation	=	$zoneLocation;
		
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
     * Set department
	 * @param \Tlt\AdmnBundle\Entity\Department $department
     *
     * @return Filter
     */	
	public function setDepartment($department)
	{
		$this->department	=	$department;
		
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
     * Set service
	 * @param \Tlt\AdmnBundle\Entity\Service $service
     *
     * @return Filter
     */
	public function setService($service)
	{
		$this->service	=	$service;
		
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
     * Set owner
	 * @param \Tlt\AdmnBundle\Entity\Owner $owner
     *
     * @return Filter
     */
	public function setOwner($owner)
	{
		$this->owner	=	$owner;
		
		return $this;
	}
}