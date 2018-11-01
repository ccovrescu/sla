<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Doctrine\Common\Collections\ArrayCollection;

/**
 * MoneyQuantity
 *
 * @ORM\Table(name="money_quantities")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\MoneyQuantityRepository")
 */
class MoneyQuantity
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
     * @var integer
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @ORM\ManyToOne(targetEntity="Owner")
	 * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */	 
    private $owner;
	
    /**
     * @ORM\ManyToOne(targetEntity="Service")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */	 
    private $service;
	
    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;	
	
	

    /**
     * Set id
     *
     * @param integer $id
     * @return MoneyQuantity
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
     * Set year
     *
     * @param integer $year
     * @return MoneyQuantity
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }
	
	
    /**
     * Set owner
     *
     * @param \Tlt\AdmnBundle\Entity\Owner $owner
     * @return MoneyQuantity
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
     * Set service
     *
     * @param \Tlt\AdmnBundle\Entity\Service $service
     * @return MoneyQuantity
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
     * Set quantity
     *
     * @param integer $quantity
     * @return MoneyQuantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
