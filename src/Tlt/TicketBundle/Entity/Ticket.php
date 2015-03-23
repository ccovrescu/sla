<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

use Tlt\AdmnBundle\Entity\AbstractEntity;
use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Equipment;

/**
 * Ticket
 *
 * @ORM\Table(name="tickets")
 * @ORM\Entity(repositoryClass="Tlt\TicketBundle\Entity\TicketRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Ticket extends AbstractEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="announced_at", type="datetime")
     */
    protected $announcedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="announced_by", type="string", length=128)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"insert"}
     * ),
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Numele sesizantului si compartimentul trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Numele sesizantului si compartimentul trebuie sa contina cel mult 128 caractere.",
     *     groups={"insert"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z0-9\-\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"insert"}
     * )
     */
    protected $announcedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_info", type="string", length=128)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"insert"}
     * ),
     * @Assert\Length(
     *     min = 4,
     *     max = 128,
     *     minMessage = "Datele de contact trebuie sa contina cel putin 4 caractere.",
     *     maxMessage = "Datele de contact trebuie sa contina cel mult 128 caractere.",
     *     groups={"insert"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z0-9@\.\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"insert"}
     * )
     */
    protected $contactInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="taken_by", type="string", length=128)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"insert"}
     * ),
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Numele celui care preia trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Numele celui care preia trebuie sa contina cel mult 128 caractere.",
     *     groups={"insert"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"insert"}
     * )
     */
    protected $takenBy;

    /**
     * @var string
     *
     * @ORM\Column(name="transmission_type", type="string", length=64)
     */
    protected $transmissionType;

    /**
     * @var string
     *
     * @ORM\Column(name="announced_to", type="string", length=128)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"insert"}
     * ),
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Persoana anuntata trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Persoana anuntata trebuie sa contina cel mult 128 caractere.",
     *     groups={"insert"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"insert"}
     * )
     */
    protected $announcedTo;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"insert"}
     * )
     */
    protected $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_real", type="boolean", nullable=true)
     */
    protected $isReal;

    /**
     * @ORM\Column(name="not_real_reason", type="string", length=256, nullable=true)
     *
     * @Assert\NotBlank(
     *     message = "Pentru un deranjament care nu este real, motivarea acestui fapt este obligatorie.",
     *     groups={"not-real"}
     * )
     */
    protected $notRealReason;

    /**
     * @ORM\OneToMany(targetEntity="TicketAllocation", mappedBy="ticket", cascade={"persist"})
     * @ORM\OrderBy({"insertedAt" = "DESC"})
     */
    protected $ticketAllocations;

    /**
     * @ORM\ManyToOne(targetEntity="TicketType")
     * @ORM\JoinColumn(name="ticket_type", referencedColumnName="id")
     *
     * @Assert\NotBlank(
     *     message = "Campul este obligatoriu. Selectati una dintre variante.",
     *     groups={"solve"}
     * )
     */
    protected $ticketType;

    /**
     * @var string
     *
     * @ORM\Column(name="compartment", type="string", length=64, nullable=true)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"solve"}
     * )
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Numele compartimentului trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Numele compartimentului trebuie sa contina cel mult 128 caractere.",
     *     groups={"solve"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"solve"}
     * )
     */
    protected $compartment;

    /**
     * @var string
     *
     * @ORM\Column(name="fixed_by", type="string", length=256, nullable=true)
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"solve"}
     * )
     * @Assert\Length(
     *     min = 7,
     *     max = 128,
     *     minMessage = "Numele celui care rezolva trebuie sa contina cel putin 7 caractere.",
     *     maxMessage = "Numele celui care rezolva trebuie sa contina cel mult 128 caractere.",
     *     groups={"solve"}
     * ),
     * @Assert\Regex(
     *     pattern = "/^[a-zA-z\s]+$/",
     *     message = "Valoarea {{ value }} contine caractere nepermise.",
     *     groups={"solve"}
     * )
     */
    protected $fixedBy;

    /**
     * @ORM\Column(name="fixed_at", type="datetime", nullable=true)
     */
    protected $fixedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Oldness")
     * @ORM\JoinColumn(name="oldness", referencedColumnName="id")
     *
     * @Assert\NotBlank(
     *     message = "Campul este obligatoriu. Selectati una dintre variante.",
     *     groups={"solve"}
     * )
     */
    protected $oldness;

    /**
     * @ORM\ManyToOne(targetEntity="BackupSolution")
     * @ORM\JoinColumn(name="backup_solution", referencedColumnName="id")
     *
     * @Assert\NotBlank(
     *     message = "Campul este obligatoriu. Selectati una dintre variante.",
     *     groups={"solve"}
     * )
     */
    protected $backupSolution;

    /**
     * @ORM\ManyToOne(targetEntity="Emergency")
     * @ORM\JoinColumn(name="emergency", referencedColumnName="id")
     *
     * @Assert\NotBlank(
     *     message = "Campul este obligatoriu. Selectati una dintre variante.",
     *     groups={"solve"}
     * )
     */
    protected $emergency;

    /**
     * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\Equipment")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(
     *     message = "Trebuie sa alegeti un echipament.",
     *     groups={"solve"}
     * )
     */
    protected $equipment;

    /**
     * @var string
     *
     * @ORM\Column(name="fixed_mode", type="string", length=256, nullable=true)
     *
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu.",
     *     groups={"solve"}
     * )
     */
    protected $fixedMode;

    /**
     * @var string
     *
     * @ORM\Column(name="resources", type="string", length=256, nullable=true)
     */
    protected $resources;

    /**
     * @ORM\OneToMany(targetEntity="TicketMapping", mappedBy="ticket", cascade={"all"}, orphanRemoval=true)
     */
    protected $ticketMapping;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_closed", type="boolean", nullable=true)
     */
    protected $isClosed;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketAllocations = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Ticket
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
     * Set announcedAt
     *
     * @param string $announcedAt
     * @return Ticket
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
     * @return Ticket
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
     * Set contactInfo
     *
     * @param string $contactInfo
     * @return Ticket
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * Get contactInfo
     *
     * @return string
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * Set takenBy
     *
     * @param string $takenBy
     * @return Ticket
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
     * Set transmissionType
     *
     * @param string $transmissionType
     * @return Ticket
     */
    public function setTransmissionType($transmissionType)
    {
        $this->transmissionType = $transmissionType;

        return $this;
    }

    /**
     * Get transmissionType
     *
     * @return string
     */
    public function getTransmissionType()
    {
        return $this->transmissionType;
    }

    /**
     * Set announcedTo
     *
     * @param string $announcedTo
     * @return Ticket
     */
    public function setAnnouncedTo($announcedTo)
    {
        $this->announcedTo = $announcedTo;

        return $this;
    }

    /**
     * Get announcedTo
     *
     * @return string
     */
    public function getAnnouncedTo()
    {
        return $this->announcedTo;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Ticket
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
     * Set notRealReason
     *
     * @param string $notRealReason
     * @return Ticket
     */
    public function setNotRealReason($notRealReason)
    {
        $this->notRealReason = $notRealReason;

        return $this;
    }

    /**
     * Get notRealReason
     *
     * @return string
     */
    public function getNotRealReason()
    {
        return $this->notRealReason;
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
     * @return Ticket
     */
    public function setTicketAllocations(Branch $branch)
    {
        $ticketAllocation = new TicketAllocation();
        $ticketAllocation->setBranch($branch);

        $ticketAllocation->seTicket($this);
        $this->ticketAllocations->add($ticketAllocation);

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
     * Add ticketAllocations
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations
     * @return Ticket
     */
    public function addTicketAllocation(TicketAllocation $ticketAllocations)
    {
        $this->ticketAllocations[] = $ticketAllocations;

        return $this;
    }

    /**
     * Remove ticketAllocations
     *
     * @param \Tlt\TicketBundle\Entity\TicketAllocation $ticketAllocations
     */
    public function removeTicketAllocation(TicketAllocation $ticketAllocations)
    {
        $this->ticketAllocations->removeElement($ticketAllocations);
    }

    /**
     * Set isReal
     *
     * @param boolean $isReal
     * @return Ticket
     */
    public function setIsReal($isReal)
    {
        $this->isReal = $isReal;

        return $this;
    }

    /**
     * Get isReal
     *
     * @return boolean
     */
    public function getIsReal()
    {
        return $this->isReal;
    }

    /**
     * Set ticketType
     *
     * @param integer $ticketType
     * @return Ticket
     */
    public function setTicketType($ticketType)
    {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * Get ticketType
     *
     * @return integer
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }

    /**
     * Set fixedBy
     *
     * @param string $fixedBy
     * @return Ticket
     */
    public function setFixedBy($fixedBy)
    {
        $this->fixedBy = $fixedBy;

        return $this;
    }

    /**
     * Get fixedBy
     *
     * @return string
     */
    public function getFixedBy()
    {
        return $this->fixedBy;
    }

    /**
     * Set fixedAt
     *
     * @param string $fixedAt
     * @return Ticket
     */
    public function setFixedAt($fixedAt)
    {
        $this->fixedAt = $fixedAt;

        return $this;
    }

    /**
     * Get fixedAt
     *
     * @return string
     */
    public function getFixedAt()
    {
        return $this->fixedAt;
    }

    /**
     * Set backupSolution
     *
     * @param BackupSolution $backupSolution
     * @return Ticket
     */
    public function setBackupSolution($backupSolution)
    {
        $this->backupSolution = $backupSolution;

        return $this;
    }

    /**
     * Get backupSolution
     *
     * @return BackupSolution
     */
    public function getBackupSolution()
    {
        return $this->backupSolution;
    }

    /**
     * Get equipment
     *
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set equipment
     *
     * @param Equipment $equipment
     * @return Ticket
     */
    public function setEquipment(Equipment $equipment)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get fixedMode
     *
     * @return string
     */
    public function getFixedMode()
    {
        return $this->fixedMode;
    }

    /**
     * Set fixedMode
     *
     * @param string $fixedMode
     * @return Ticket
     */
    public function setFixedMode($fixedMode)
    {
        $this->fixedMode = $fixedMode;

        return $this;
    }

    /**
     * Get resources
     *
     * @return string
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set resources
     *
     * @param string $resources
     * @return Ticket
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setNotRealReasonValue()
    {
        if ($this->isReal != 0)
            $this->notRealReason = null;
    }

    /**
     * Set compartment
     *
     * @param string $compartment
     * @return Ticket
     */
    public function setCompartment($compartment)
    {
        $this->compartment = $compartment;

        return $this;
    }

    /**
     * Get compartment
     *
     * @return string
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * Set oldness
     *
     * @param integer $oldness
     * @return Ticket
     */
    public function setOldness($oldness)
    {
        $this->oldness = $oldness;

        return $this;
    }

    /**
     * Get oldness
     *
     * @return integer
     */
    public function getOldness()
    {
        return $this->oldness;
    }

    /**
     * Set emergency
     *
     * @param integer $emergency
     * @return Ticket
     */
    public function setEmergency($emergency)
    {
        $this->emergency = $emergency;

        return $this;
    }

    /**
     * Get emergency
     *
     * @return integer
     */
    public function getEmergency()
    {
        return $this->emergency;
    }

    /**
     * Set isClosed
     *
     * @param boolean $isClosed
     * @return Ticket
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isClosed
     *
     * @return boolean
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Return working minutes elapsed from OCCURED moment to RESOLVED moment.
     */
    private function getWorkingTime($startDate, $endDate, $startWorkingTime = '07:30:00', $endWorkingTime = '16:30:00')
    {

        echo $startWorkingTime . ' - ' . $endWorkingTime . '<br/>';
        if (!defined('ONEMINUTE'))
            define ('ONEMINUTE', 60);

        // ESTABLISH THE MINUTES PER DAY FROM START AND END TIMES
        $startWorkingTime = strtotime($startWorkingTime);
        $endWorkingTime = strtotime($endWorkingTime);
        $minutes_per_day = (int)(($endWorkingTime - $startWorkingTime) / 60) + 1;

        // ESTABLISH THE HOLIDAYS
        $holidays = array(
            '2015-01-01',
            '2015-01-02',
            '2015-01-02',
            '2015-04-12',
            '2015-04-13',
            '2015-05-01',
            '2015-05-31',
            '2015-06-01',
            '2015-08-15',
            '2015-11-30',
            '2015-12-01',
            '2015-12-25',
            '2015-12-26',
        );

        // Convert to TIMESTAMP
        // $start	= strtotime($startDate);
        // $end	= strtotime($endDate);
        $start = $startDate->getTimestamp();
        $end = $endDate->getTimestamp();

        // RESET WORK MINUTES
        $workminutes = 0;

        // ITERATE OVER THE DAYS
        $start = $start - ONEMINUTE;
        while ($start < $end) {
            $start = $start + ONEMINUTE;

            // ELIMINATE WEEKENDS - SAT AND SUN
            $weekday = date('D', $start);
            if (substr($weekday, 0, 1) == 'S') continue;

            // ELIMINATE HOLIDAYS
            $iso_date = date('Y-m-d', $start);
            if (in_array($iso_date, $holidays)) continue;

            // ELIMINATE HOURS BEFORE BUSINESS HOURS
            $daytime = date('H:i:s', $start);
            if (($daytime < date('H:i:s', $startWorkingTime))) continue;

            // ELIMINATE HOURS PAST BUSINESS HOURS
            $daytime = date('H:i:s', $start);
            if (($daytime > date('H:i:s', $endWorkingTime))) continue;

            $workminutes++;
        } // end while

        $workminutes = $workminutes - (ceil($workminutes / $minutes_per_day) > 1 ? ceil($workminutes / $minutes_per_day) - 1 : 1);

        return ($workminutes > 0 ? $workminutes : 0);
    }

    /**
     * Add ticketMapping
     *
     * @param \Tlt\TicketBundle\Entity\TicketMapping $ticketMapping
     * @return Ticket
     */
    public function addTicketMapping(TicketMapping $ticketMapping)
    {
        $ticketMapping->setResolvedIn(
            $this->getWorkingTime(
                $this->announcedAt,
                $this->fixedAt,
                $ticketMapping->getMapping()->getSystem()->getGuaranteedValues()->first()->getMinHour()->format('H:i:s'),
                $ticketMapping->getMapping()->getSystem()->getGuaranteedValues()->first()->getMaxHour()->format('H:i:s')
            )
        );
        $this->ticketMapping[] = $ticketMapping;

        return $this;
    }

    /**
     * Remove ticketMapping
     *
     * @param \Tlt\TicketBundle\Entity\TicketMapping $ticketMapping
     */
    public function removeTicketMapping(TicketMapping $ticketMapping)
    {
        $this->ticketMapping->removeElement($ticketMapping);
        $ticketMapping->setMapping(null);
        $ticketMapping->setTicket(null);
    }

    /**
     * Get ticketMapping
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketMapping()
    {
        return $this->ticketMapping;
    }
}