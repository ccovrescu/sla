<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TicketEquipment
 *
 * @ORM\Table(name="ticket_equipments")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class TicketEquipment
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="TicketCreate", inversedBy="ticketEquipments")
	 * @ORM\JoinColumn(name="ticket_create", referencedColumnName="id")
	 */
	protected $ticketCreate;

	/**
	 * @ORM\OneToOne(targetEntity="\Tlt\AdmnBundle\Entity\Equipment")
	 * @ORM\JoinColumn(name="equipment", referencedColumnName="id")
	 */
	protected $equipment;
	
	/**
	 * @ORM\OneToMany(targetEntity="TicketSystem", mappedBy="ticketEquipment", cascade={"persist"})
	 */
	protected $ticketSystems;
	
	public $branch;
	public $location;
	public $service;
	
	/**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketSystems = new ArrayCollection();
    }
	
    /**
     * Set id
     *
     * @param integer $id
	 * @return TicketEquipment
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
	 * @return TicketEquipment
     */
    public function setTicketCreate($ticketCreate)
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
     * Set equipment
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipment
	 * @return TicketEquipment
     */
    public function setEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment)
    {
        $this->equipment = $equipment;
		
		return $this;
    }

    /**
     * Get equipment
     *
     * @return Tlt\AdmnBundle\Entity\Equipment 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
	
	
	public function getTicketSystems()
	{
		return $this->ticketSystems;
	}
	
	public function addTicketSystems(\Tlt\AdmnBundle\Entity\System $system)
	{
		$ticketSystem = new TicketSystem();
		$ticketSystem->setTicketEquipment( $this );
		$ticketSystem->setSystem( $system );
		
		$this->ticketSystems[] =  $ticketSystem ;
		
		return $this;
			
	}
	
	public function setTicketSystems()
	{
		$this->ticketSystems = new ArrayCollection();
		return $this;
	}
}