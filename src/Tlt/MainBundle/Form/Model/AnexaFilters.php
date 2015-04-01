<?php
namespace Tlt\MainBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AnexaFilters
{
    /**
     * @Assert\Type(type="Tlt\AdmnBundle\Entity\Owner")
     * @Assert\Valid()
     */
    protected $owner;

    /**
     * @var integer
     *
     */
    protected $department;

    /**
     * @var integer
     *
     */
    protected $year;


    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
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