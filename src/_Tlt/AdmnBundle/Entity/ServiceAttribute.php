<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ServiceAttribute
 *
 * @ORM\Table(name="service_attr")
 * @ORM\Entity
 */
class ServiceAttribute
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
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;
	
	/**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="service")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */	 
    private $service;

   /**
     * @ORM\OneToMany(targetEntity="ServiceAttributeValue", mappedBy="service_attr_values")
     */
    private $service_attr_values;

	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service_attr_values = new ArrayCollection();
    }
	
     /**
     * Set name
     *
     * @param integer $id
     * @return ServiceAttribute
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
     * @return ServiceAttribute
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
     * Set department
     *
     * @param \Tlt\AdmneBundle\Entity\Service $service
     * @return ServiceAttribute
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
	
	public function __toString()
	{
		return $this->getName();
	}
}