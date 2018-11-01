<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoneyPrice
 *
 * @ORM\Table(name="money_price")
 * @ORM\Entity()
 */
class MoneyPrice
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
     * @ORM\ManyToOne(targetEntity="Service")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */	 
    private $service;
	
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;	
	
	
	
	

    /**
     * Set id
     *
     * @param integer $id
     * @return MoneyPrice
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
     * @return MoneyPrice
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
     * Set service
     *
     * @param \Tlt\AdmnBundle\Entity\Service $service
     * @return MoneyPrice
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
     * Set price
     *
     * @param float $price
     * @return MoneyPrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }
}
