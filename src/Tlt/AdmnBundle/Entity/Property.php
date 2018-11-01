<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 *
 * @ORM\Table(name="properties")
 * @ORM\Entity
 */
class Property extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="PropertyValue", mappedBy="property")
     */
    protected $propertyValues;

	
    public function __construct()
    {
        $this->propertyValues = new ArrayCollection();
    }		


    /**
     * Set id
	 * @param integer $id
     *
     * @return Property 
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Property
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
     * Add propertyValues
     *
     * @param \Tlt\AdmnBundle\Entity\PropertyValue $propertyValues
     * @return Property
     */
    public function addPropertyValue(\Tlt\AdmnBundle\Entity\PropertyValue $propertyValues)
    {
        $this->propertyValues[] = $propertyValues;

        return $this;
    }

    /**
     * Remove propertyValues
     *
     * @param \Tlt\AdmnBundle\Entity\PropertyValue $propertyValues
     */
    public function removePropertyValue(\Tlt\AdmnBundle\Entity\PropertyValue $propertyValues)
    {
        $this->propertyValues->removeElement($propertyValues);
    }

    /**
     * Get propertyValues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropertyValues()
    {
        return $this->propertyValues;
    }
}
