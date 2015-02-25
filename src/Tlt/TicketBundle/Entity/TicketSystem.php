<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Tlt\AdmnBundle\Entity\System;

/**
 * TTicketSystem
 *
 * @ORM\Table(name="ticket_systems")
 * @ORM\Entity
 */
class TicketSystem
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="TicketEquipment", inversedBy="ticketSystems")
	 * @ORM\JoinColumn(name="ticket_equipment", referencedColumnName="id")
	 */
	protected $ticketEquipment;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Tlt\AdmnBundle\Entity\System")
	 * @ORM\JoinColumn(name="system", referencedColumnName="id")
	 */


	protected $system;
	
	
	/**
     * Set id
     *
     * @param integer $id
     * @return TicketSystem
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
     * Set ticketEquipment
     *
     * @param TicketEquipment $ticketEquipment
     * @return TicketSystem
     */
    public function setTicketEquipment($ticketEquipment)
    {
        $this->ticketEquipment = $ticketEquipment;

        return $this;
    }

    /**
     * Get ticketEquipment
     *
     * @return TicketEquipment 
     */
    public function getTicketEquipment()
    {
        return $this->ticketEquipment;
    }
	
	/**
     * Set system
     *
     * @param System $system
     * @return TicketSystem
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
}