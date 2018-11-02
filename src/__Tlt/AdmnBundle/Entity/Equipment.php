<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Equipment
 *
 * @ORM\Table(name="equipments")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\EquipmentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Equipment extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Owner", inversedBy="equipments")
	 * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */	 
    private $owner;
	
    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="equipments")
	 * @ORM\JoinColumn(name="location", referencedColumnName="id")
     */	 
    private $location;
	
    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="equipments")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */	 
    private $service;
	
	/**
	 * @var integer
     *
     * @ORM\Column(name="total", type="integer")
	 */
	private $total;
	
	/**
	 * @var boolean
     *
     * @ORM\Column(name="in_pam", type="boolean")
	 */
	private $inPam;	
	
   /**
     * @ORM\OneToMany(targetEntity="ServiceAttributeValue", mappedBy="equipment")
     */
    private $service_attr_values;
	
	
	
	protected $branch;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service_attr_values = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Equipment
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
     * @return Equipment
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
	
	
    /**
     * Set owner
     *
     * @param \Tlt\AdmnBundle\Entity\Owner $owner
     * @return Equipment
     */
    public function setOwner(\Tlt\AdmnBundle\Entity\Owner $owner = null)
    {
        $this->owner = $owner;

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
     * Set location
     *
     * @param \Tlt\AdmnBundle\Entity\Location $location
     * @return Equipment
     */
    public function setLocation(\Tlt\AdmnBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \Tlt\AdmnBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set service
     *
     * @param \Tlt\AdmnBundle\Entity\Service $service
     * @return Equipment
     */
    public function setService(\Tlt\AdmnBundle\Entity\Service $service = null)
    {
        $this->service = $service;

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
     * Set total
     *
     * @param integer $total
     * @return Equipment
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer 
     */
    public function getTotal()
    {
        return $this->total;
    }
	
	
    /**
     * Set inPam
     *
     * @param boolean $inPam
     * @return Equipment
     */
    public function setInPam($inPam)
    {
        $this->inPam = $inPam;

        return $this;
    }

    /**
     * Get inPam
     *
     * @return boolean 
     */
    public function getInPam()
    {
        return $this->inPam;
    }


	/**
     * Add service_attr_value
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceAttributeValue $service_attr_value
     * @return Equipment
     */
    public function addServiceAttributeValue(\Tlt\AdmnBundle\Entity\ServiceAttributeValue $service_attr_value)
    {
        $this->service_attr_values[] = $service_attr_value;

        return $this;
    }

    /**
     * Remove service_attr_value
     *
     * @param \Tlt\AdmnBundle\Entity\ServiceAttributeValue $service_attr_value
     */
    public function removeServiceAttributeValue(\Tlt\AdmnBundle\Entity\ServiceAttributeValue $service_attr_value)
    {
        $this->service_attr_values->removeElement($service_attr_value);
    }

    /**
     * Get service_attr_values
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServiceAttributeValues()
    {
        return $this->service_attr_values;
    }
	
	public function getUniqueName()
	{
		// $sav = $this->getServiceAttributeValues();
		
		// foreach ($sav as $i)
			// $text .= $i->getValue();
		// return 'text1 - ' . $text;
		
		switch ($this->service->getId()) {
			case 3:
					$text = '';
					foreach ($this->service_attr_values as $sav)
						$text .= str_pad($sav->getValue(), 16,' ') . '*';
					return $text;
				break;
			default:
					return $this->name;
				break;
		}
	}
	
	public function __toString()
	{
		return $this->name;
	}
	
	public function setBranch(\Tlt\AdmnBundle\Entity\Branch $branch)
	{
		$this->branch = $branch;
	}
	
	public function getBranch()
	{
		return $this->branch;
	}
}
