<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

use Tlt\AdmnBundle\Entity\AbstractEntity;
use Tlt\AdmnBundle\Entity\Branch;

/**
 * TicketCreate
 *
 * @ORM\Table(name="ticket_create")
 * @ORM\Entity(repositoryClass="Tlt\TicketBundle\Entity\TicketCreateRepository")
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
     *
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Numele sesizantului trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Numele sesizantului trebuie sa contina cel mult 128 caractere."
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere invalide."
     * )
     */
	protected $announcedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="taken_by", type="string", length=128)
     */
    protected $takenBy;

    /**
     * @var string
     *
     * @ORM\Column(name="sent_type", type="string", length=64)
     */
    protected $sentType;

    /**
	* @ORM\Column(type="string", length=255)
	*/
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="TicketAllocation", mappedBy="ticketCreate", cascade={"persist"})
     * @ORM\OrderBy({"insertedAt" = "DESC"})
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
     * @param Branch $branch
     * @return TicketCreate
     */
    public function setTicketAllocations(Branch $branch)
    {
		$ticketAllocation = new TicketAllocation();
		$ticketAllocation->setBranch( $branch );

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

    public function updateTicketAllocation()
    {
        $ticketAllocation = $this->ticketAllocations->last();
        $this->ticketAllocations->removeElement($ticketAllocation);

        $ticketAllocation->setInsertedBy($this->getInsertedBy());
        $ticketAllocation->setModifiedBy($this->getModifiedBy());
        $ticketAllocation->setFromHost($this->getFromHost());

        $this->ticketAllocations->add($ticketAllocation);
    }

    /**
     * Set takenBy
     *
     * @param string $takenBy
     * @return TicketCreate
     */
    public function setTakenBy($takenBy)
    {
        $this->takenBy = $takenBy;

        return $this;
    }

    /**
     * Get takenBy
     *
     * @return string 
     */
    public function getTakenBy()
    {
        return $this->takenBy;
    }

    /**
     * Set sentType
     *
     * @param string $sentType
     * @return TicketCreate
     */
    public function setSentType($sentType)
    {
        $this->sentType = $sentType;

        return $this;
    }

    /**
     * Get sentType
     *
     * @return string 
     */
    public function getSentType()
    {
        return $this->sentType;
    }

    /**
     * Add ticketAllocations
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations
     * @return TicketCreate
     */
    public function addTicketAllocation(\Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations)
    {
        $this->ticketAllocations[] = $ticketAllocations;

        return $this;
    }

    /**
     * Remove ticketAllocations
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations
     */
    public function removeTicketAllocation(\Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations)
    {
        $this->ticketAllocations->removeElement($ticketAllocations);
    }

    /**
     * Add ticketEquipments
     *
     * @param \Tlt\TicketBundle\Entity\TicketEquipment $ticketEquipments
     * @return TicketCreate
     */
    public function addTicketEquipment(\Tlt\TicketBundle\Entity\TicketEquipment $ticketEquipments)
    {
        $this->ticketEquipments[] = $ticketEquipments;

        return $this;
    }

    /**
     * Remove ticketEquipments
     *
     * @param \Tlt\TicketBundle\Entity\TicketEquipment $ticketEquipments
     */
    public function removeTicketEquipment(\Tlt\TicketBundle\Entity\TicketEquipment $ticketEquipments)
    {
        $this->ticketEquipments->removeElement($ticketEquipments);
    }

    /**
     * Set ticketFix
     *
     * @param \Tlt\TicketBundle\Entity\TicketFix $ticketFix
     * @return TicketCreate
     */
    public function setTicketFix(\Tlt\TicketBundle\Entity\TicketFix $ticketFix = null)
    {
        $this->ticketFix = $ticketFix;

        return $this;
    }
}
