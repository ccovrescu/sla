<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Branch
 *
 * @ORM\Table(name="branches")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\BranchRepository")
 */
class Branch extends AbstractEntity
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
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="emails", type="string", length=256)
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="ZoneLocation", mappedBy="branch")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $zoneLocations;

    /**
     * @ORM\OneToMany(targetEntity="Tlt\TicketBundle\Entity\TicketAllocation", mappedBy="branch")
     */
    private $ticketAllocation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->zoneLocations = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Branch
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
     * @return Branch
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
     * Add zoneLocation
     *
     * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation
     * @return Branch
     */
    public function addZoneLocation(\Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation)
    {
        $this->zoneLocations[] = $zoneLocation;

        return $this;
    }

    /**
     * Remove zoneLocation
     *
     * @param \Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation
     */
    public function removeZoneLocation(\Tlt\AdmnBundle\Entity\ZoneLocation $zoneLocation)
    {
        $this->zoneLocations->removeElement($zoneLocation);
    }

    /**
     * Get zoneLocations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getZoneLocations()
    {
        return $this->zoneLocations;
    }

    /**
     * Get ticketAllocation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketAllocations()
    {
        return $this->ticketAllocation;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Set emails
     *
     * @param string $emails
     * @return Branch
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Get emails
     *
     * @return string 
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add ticketAllocation
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocation
     * @return Branch
     */
    public function addTicketAllocation(\Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocation)
    {
        $this->ticketAllocation[] = $ticketAllocation;

        return $this;
    }

    /**
     * Remove ticketAllocation
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocation
     */
    public function removeTicketAllocation(\Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocation)
    {
        $this->ticketAllocation->removeElement($ticketAllocation);
    }

    /**
     * Get ticketAllocation
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTicketAllocation()
    {
        return $this->ticketAllocation;
    }
}
