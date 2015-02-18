<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GuaranteedValue
 *
 * @ORM\Table(name="guaranteed_values")
 * @ORM\Entity()
 */
class GuaranteedValue
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
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;
	
    /**
     * @ORM\ManyToOne(targetEntity="System", inversedBy="guaranteedValues")
	 * @ORM\JoinColumn(name="system", referencedColumnName="id")
     */	 
    private $system;	
	
	/**
	 * @var \time
     *
     * @ORM\Column(name="min_hour", type="time")
	 */
	private $minHour;

	/**
	 * @var \time
     *
     * @ORM\Column(name="max_hour", type="time")
	 */
	private $maxHour;
	
	
    /**
     * Set id
     *
     * @param integer $id
     * @return GuaranteedValue
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
     * Set system
     *
     * @param System $system
     * @return GuaranteedValue
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return System 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return GuaranteedValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }
}