<?php

namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tlt\AdmnBundle\Entity\Owner;

/**
* TicketAllocation
*
* @ORM\Table(name="ticket_allocations")
* @ORM\Entity
* @ORM\HasLifecycleCallbacks()
*/
class TicketAllocation
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
    * @ORM\ManyToOne(targetEntity="TicketCreate", inversedBy="ticketAllocations")
	* @ORM\JoinColumn(name="ticket_create", referencedColumnName="id")
	*/	
	protected $ticketCreate;
	
	/**
    * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\Owner", inversedBy="ticketAllocation")
	* @ORM\JoinColumn(name="owner", referencedColumnName="id")
	*/	
	protected $owner;
	
	/**
	* @ORM\Column(name="allocated_at", type="datetime")
	*/
	protected $allocatedAt;
	
	/**
	* @ORM\Column(name="allocated_by", type="string")
	*/
	protected $allocatedBy;
	
    /**
     * Set id
     *
     * @param integer $id
	 * @return TicketAllocationation
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
     * Set ticketCreate
     *
     * @param TicketCreate $ticketCreate
     * @return TicketAllocationation
     */
    public function setTicketCreate(TicketCreate $ticketCreate = null)
    {
        $this->ticketCreate = $ticketCreate;

        return $this;
    }
	
    /**
     * Get ticketCreate
     *
     * @return TicketCreate
     */
    public function getTicketCreate()
    {
        return $this->ticketCreate;
    }
	
    /**
     * Set owner
     *
     * @param Owner $owner
     * @return TicketAllocation
     */
    public function setOwner(Owner $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

	/**
	* @ORM\PrePersist
	*/
	public function setAllocatedAt()
	{
		$this->allocatedAt = new \DateTime();
	}
	
	public function getAllocatedAt()
	{
		return $this->allocatedAt;
	}
	
	public function setAllocatedBy($allocatedBy)
	{
			$this->allocatedBy = $allocatedBy;
	}
	
	public function getAllocatedBy()
	{
		return $this->allocatedBy;
	}
}