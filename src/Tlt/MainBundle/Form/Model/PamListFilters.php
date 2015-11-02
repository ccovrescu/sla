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
}