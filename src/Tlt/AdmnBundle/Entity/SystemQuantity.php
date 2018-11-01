<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SystemQuantity
 *
 * @ORM\Table(name="system_quantity")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\SystemQuantityRepository")
 */
class SystemQuantity
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
     * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\Owner")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\System")
     * @ORM\JoinColumn(name="system", referencedColumnName="id")
     */
    private $system;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;


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
     * Set owner
     *
     * @param integer $owner
     * @return SystemQuantity
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return integer 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set system
     *
     * @param integer $system
     * @return SystemQuantity
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return integer 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return SystemQuantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return SystemQuantity
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return SystemQuantity
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}
