<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tlt\MainBundle\Model\TimeCalculation;

/**
 * Owner
 *
 * @ORM\Table(name="working_time")
 * @ORM\Entity()
 */
class WorkingTime extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="GuaranteedValue", mappedBy="workingTime")
     */
    private $guaranteedValues;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->guaranteedValues = new ArrayCollection();
    }


    /**
     * Set id
     *
     * @param integer $id
     * @return Owner
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
     * Set minHour
     *
     * @param \DateTime $minHour
     * @return GuaranteedValue
     */
    public function setMinHour($minHour)
    {
        $this->minHour = $minHour;

        return $this;
    }

    /**
     * Get minHour
     *
     * @return \DateTime
     */
    public function getMinHour()
    {
        return $this->minHour;
    }

    /**
     * Set maxHour
     *
     * @param \DateTime $maxHour
     * @return GuaranteedValue
     */
    public function setMaxHour($maxHour)
    {
        $this->maxHour = $maxHour;

        return $this;
    }

    /**
     * Get maxHour
     *
     * @return \DateTime
     */
    public function getMaxHour()
    {
        return $this->maxHour;
    }


    /**
     * Add guaranteedValue
     *
     * @param GuaranteedValue $guaranteedValue
     * @return WorkingTime
     */
    public function addGuaranteedValue(GuaranteedValue $guaranteedValue)
    {
        $guaranteedValue->setWorkingTime( $this );
        $this->guaranteedValues[] = $guaranteedValue;

        return $this;
    }

    /**
     * Remove guaranteedValue
     *
     * @param GuaranteedValue $guaranteedValue
     */
    public function removeGuaranteedValue(GuaranteedValue $guaranteedValue)
    {
        $this->guaranteedValues->removeElement($guaranteedValue);
    }

    /**
     * Get guaranteedValues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuaranteedValues()
    {
        return $this->guaranteedValues;
    }
}
