<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tlt\AdmnBundle\Entity\AbstractEntity;

/**
 * TicketCreate
 *
 * @ORM\Table(name="ticket_create")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class TicketCreate extends AbstractEntity
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
	* @ORM\Column(name="inserted_at", type="datetime")
	*/
	// protected $insertedAt;

	/**
	* @ORM\Column(name="occured_at", type="datetime")
	*/
	protected $occuredAt;

	/**
	* @ORM\Column(name="announced_at", type="datetime")
	*/
	protected $announcedAt;

	/**
	* @var string
	*
	* @ORM\Column(name="announced_by", type="string", length=128)
	*/
	protected $announcedBy;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="TicketAllocation", mappedBy="ticketCreate", cascade={"persist"})
	 */
	protected $ticketAllocations;
	
	/**
	 * @ORM\OneToMany(targetEntity="TicketEquipment", mappedBy="ticketCreate", cascade={"persist"})
	 */
	protected $ticketEquipments;
	
	/**
	 * @ORM\OneToOne(targetEntity="TicketFix", mappedBy="ticketCreate", cascade={"persist"})
	 */
	protected $ticketFix;
	
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketAllocations = new ArrayCollection();
		$this->ticketEquipments = new ArrayCollection();
    }
	
	
    /**
     * Set id
     *
     * @param integer $id
	 * @return TicketCreate
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
     * Get insertedAt
     *
     * @return DateTime 
     */
    // public function getInsertedAt()
    // {
        // return $this->insertedAt;
    // }
	
	
    /**
     * Set occuredAt
     *
     * @param string $occuredAt
	 * @return TicketCreate
     */
    public function setOccuredAt($occuredAt)
    {
        $this->occuredAt = $occuredAt;
		
		return $this;
    }
	
    /**
     * Get occuredAt
     *
     * @return string 
     */
    public function getOccuredAt()
    {
        return $this->occuredAt;
    }
	
	
    /**
     * Set announcedAt
     *
     * @param string $announcedAt
	 * @return TicketCreate
     */
    public function setAnnouncedAt($announcedAt)
    {
        $this->announcedAt = $announcedAt;
		
		return $this;
    }

    /**
     * Get announcedAt
     *
     * @return string 
     */
    public function getAnnouncedAt()
    {
        return $this->announcedAt;
    }

	
    /**
     * Set announcedBy
     *
     * @param string $announcedBy
     * @return TicketCreate
     */
    public function setAnnouncedBy($announcedBy)
    {
        $this->announcedBy = $announcedBy;

        return $this;
    }

    /**
     * Get announcedBy
     *
     * @return string 
     */
    public function getAnnouncedBy()
    {
        return $this->announcedBy;
    }
	

    /**
     * Set description
     *
     * @param string $description
     * @return TicketCreate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
	
    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get ticketAllocations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketAllocations()
    {
        return $this->ticketAllocations;
    }

	/**
     * Set ticketAllocations
     *
     * @param \Tlt\AdmnBundle\Entity\Owner $owner
     * @return TicketCreate
     */
    public function setTicketAllocations(\Tlt\AdmnBundle\Entity\Owner $owner)
    {
		$ticketAllocation = new TicketAllocation();
		$ticketAllocation->setOwner( $owner );
		$ticketAllocation->setAllocatedBy('test-allocated-by');
		
		// set tticketIns for update tticketIns ID to database.
		$ticketAllocation->setTicketCreate($this);
		
        $this->ticketAllocations->add( $ticketAllocation );

        return $this;
    }
	
	
    /**
     * Get ticketEquipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketEquipments()
    {
        return $this->ticketEquipments;
    }	
	
	/**
     * Set ticketEquipments
     *
     * @param TicketEquipment $ticketEquipment
     * @return TicketCreate
     */
    public function addTicketEquipments(TicketEquipment $ticketEquipment)
    {
		$ticketEquipment->setTicketCreate( $this );
        $this->ticketEquipments->add( $ticketEquipment );

        return $this;
    }
	

    /**
     * Get ticketFix
     *
     * @return TicketFix
     */
    public function getTicketFix()
    {
        return $this->ticketFix;
    }	
	
	/**
     * Set ticketFix
     *
     * @param TicketFix $ticketFix
     * @return TicketCreate
     */
    public function addTicketFix(TicketFix $ticketFix)
    {
		$ticketFix->setTicketCreate( $this );
        $this->ticketFix = $ticketFix;

        return $this;
    }	

	
    /**
     * Gets triggered only on insert
     * 
     * @ORM\PrePersist
     */
    // public function onPrePersist()
    // {
        // $this->insertedAt = new \DateTime("now");
    // }
}