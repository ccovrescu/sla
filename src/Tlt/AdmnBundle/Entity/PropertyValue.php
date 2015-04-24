<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PropertyValue
 *
 * @ORM\Table(name="properties_values")
 * @ORM\Entity
 */
class PropertyValue extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="propertiesValues")
	 * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */	 
    private $equipment;

    /**
     * @ORM\ManyToOne(targetEntity="Property", inversedBy="propertyValues", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="property_id", referencedColumnName="id")
     * @ORM\OrderBy({"name" = "ASC"})
     */	 
    private $property;
	
    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=64)
     */
    private $value;


    /**
     * Set id
     *
     * @param string $id
     * @return PropertyValue
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
     * Set equipment
     *
     * @param \Tlt\AdmneBundle\Entity\Equipment $equipment
     * @return PropertyValue
     */
    public function setEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \Tlt\AdmnBundle\Entity\Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }	

    /**
     * Set property
     *
     * @param \Tlt\AdmnBundle\Entity\Property $property
     * @return PropertyValue
     */
    public function setProperty(\Tlt\AdmnBundle\Entity\Property $property = null)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return \Tlt\AdmnBundle\Entity\Property
     */
    public function getProperty()
    {
        return $this->property;
    }	
	
    /**
     * Set value
     *
     * @param string $value
     * @return PropertyValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }
} 