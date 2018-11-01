<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 4/27/2015
 * Time: 11:45 AM
 */

namespace Tlt\MainBundle\Form\Model;

class PamListFilters
{
    /**
     * @var \Tlt\AdmnBundle\Entity\Owner
     */
    protected $owner;

    /**
     * @var integer
     *
     */
    protected $department;

    /**
     * @var \Tlt\AdmnBundle\Entity\Service
     */
    private $service;

    /**
     * @var \Tlt\AdmnBundle\Entity\System
     */
    private $system;

    /**
     * @return \Tlt\AdmnBundle\Entity\Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \Tlt\AdmnBundle\Entity\Owner $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param int $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
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
     * Get system
     *
     * @return \Tlt\AdmnBundle\Entity\System
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set system
     * @param \Tlt\AdmnBundle\Entity\System $system
     *
     * @return Filter
     */
    public function setSystem($system)
    {
        $this->system	=	$system;

        return $this;
    }
}