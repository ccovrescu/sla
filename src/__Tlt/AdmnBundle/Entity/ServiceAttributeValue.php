<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceAttributeValue
 *
 * @ORM\Table(name="service_attr_values")
 * @ORM\Entity
 */
class ServiceAttributeValue extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="service_attr_values")
	 * @ORM\JoinColumn(name="equipment", referencedColumnName="id")
     */	 
    private $equipment;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceAttribute", inversedBy="service_attr")
	 * @ORM\JoinColumn(name="service_attr", referencedColumnName="id")
     */	 
    private $service_attr;
	
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
     * @return ServiceAttributeValue
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
     * @return ServiceAttributeValue
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
     * Set service_attr
     *
     * @param \Tlt\AdmneBundle\Entity\ServiceAttribute $service_attr
     * @return ServiceAttributeValue
     */
    public function setServiceAttr(\Tlt\AdmnBundle\Entity\ServiceAttribute $service_attr = null)
    {
        $this->service_attr = $service_attr;

        return $this;
    }

    /**
     * Get service_attr
     *
     * @return \Tlt\AdmnBundle\Entity\ServiceAttribute
     */
    public function getServiceAttr()
    {
        return $this->service_attr;
    }	
	
    /**
     * Set value
     *
     * @param string $value
     * @return ServiceAttributeValue
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
