<?php
namespace Tlt\AdmnBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tlt\AdmnBundle\Entity\Equipment;

class EquipmentSelect
{
    /**
     * @Assert\Type(type="Tlt\AdmnBundle\Entity\Equipment")
     * @Assert\Valid()
     */
    protected $equipment;

    /**
     * @Assert\Type(type="Tlt\AdmnBundle\Entity\Owner")
     * @Assert\Valid()
     */
    protected $owner;
	
    /**
     * @Assert\Type(type="Tlt\AdmnBundle\Entity\Branch")
     * @Assert\Valid()
     */
    protected $branch;

    /**
     * @Assert\Type(type="Tlt\AdmnBundle\Entity\Location")
     * @Assert\Valid()
     */
    protected $location;
	
	
	public function setEquipment($equipment)
	{
		$this->equipment = $equipment;
	}
	
	public function getEquipment()
	{
		return $this->equipment;
	}
	
	
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
	
	public function getOwner()
	{
		return $this->owner;
	}
	

	public function setBranch($branch)
	{
		$this->branch = $branch;
	}
	
	public function getBranch()
	{
		return $this->branch;
	}
	
	
	public function setLocation($location)
	{
		$this->location = $location;
	}
	
	public function getLocation()
	{
		return $this->location;
	}
}	