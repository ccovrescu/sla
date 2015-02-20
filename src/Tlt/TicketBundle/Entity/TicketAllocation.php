<?php

namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tlt\AdmnBundle\Entity\AbstractEntity;
use Tlt\AdmnBundle\Entity\Branch;

/**
* TicketAllocation
*
* @ORM\Table(name="ticket_allocations")
* @ORM\Entity
*/
class TicketAllocation extends AbstractEntity
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
    * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\Branch", inversedBy="ticketAllocation")
	* @ORM\JoinColumn(name="branch", referencedColumnName="id")
	*/	
	protected $branch;
	
    /**
     * Set id
     *
     * @param integer $id
	 * @return TicketAllocation
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
     * @return TicketAllocation
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
     * Set branch
     *
     * @param Branch $branch
     * @return TicketAllocation
     */
    public function setBranch(Branch $branch = null)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }
}