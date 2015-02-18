<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class FilterDS
{
	/**
     * @var \Tlt\AdmnBundle\Entity\Department
	 */
	private $department;
	
	/**
     * @var \Tlt\AdmnBundle\Entity\Service
	 */
	 private $service;
	 
	
	
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
}