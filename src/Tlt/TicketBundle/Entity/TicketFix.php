<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tlt\AdmnBundle\Entity\AbstractEntity;

/**
 * TicketFix
 *
 * @ORM\Table(name="ticket_fix")
 * @ORM\Entity(repositoryClass="Tlt\TicketBundle\Entity\TicketFixRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TicketFix extends AbstractEntity
{
	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;
	
	/**
     * @ORM\OneToOne(targetEntity="TicketCreate", inversedBy="ticketFix")
	 * @ORM\JoinColumn(name="ticket_create", referencedColumnName="id")
	 */
	protected $ticketCreate;

	/**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=256)
     */
	protected $obs;

	/**
     * @var boolean
     *
     * @ORM\Column(name="is_real", type="boolean")
     */
	protected $isReal;
	
	/**
	 * @ORM\Column(name="resolved_at", type="datetime")
	 */
	protected $resolvedAt;
	
	/**
	 * @ORM\Column(name="not_affected_reason", type="string", length=256, nullable=true)
	 */	
	protected $notAffectedReason;
	
	/**
	 * @ORM\Column(name="resolved_in", type="integer")
	 */	
	protected $resolvedIn;
	
	
	/**
     * Set id
     *
     * @param integer $id
     * @return TicketFix
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
     * @return TicketFix
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
     * Set obs
     *
     * @param string $obs
     * @return TicketFix
     */
    public function setObs($obs)
    {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }


	/**
     * Set isReal
     *
     * @param boolean $isReal
     * @return TicketFix
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
     * Set resolvedAt
     *
     * @param string $resolvedAt
	 * @return TicketFix
     */
    public function setResolvedAt($resolvedAt)
    {
        $this->resolvedAt = $resolvedAt;
		
		return $this;
    }
	
    /**
     * Get resolvedAt
     *
     * @return string 
     */
    public function getResolvedAt()
    {
        return $this->resolvedAt;
    }
	
	public function getResolvedIn()
	{
		return $this->resolvedIn;
	}

	/**
     * Set notAffectedReason
     *
     * @param string $notAffectedReason
     * @return TicketFix
     */
    public function setNotAffectedReason($notAffectedReason)
    {
        $this->notAffectedReason = $notAffectedReason;

        return $this;
    }

    /**
     * Get notAffectedReason
     *
     * @return string 
     */
    public function getNotAffectedReason()
    {
        return $this->notAffectedReason;
    }
	
    /**
     * Gets triggered only on insert
     * 
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->resolvedIn = $this->getWorkingTime($this->ticketCreate->getOccuredAt(), $this->resolvedAt);
    }	
	
	public function hasSystems()
	{
		$ticketEquipments = $this->getTicketCreate()->getTicketEquipments();
		foreach ($ticketEquipments as $ticketEquipment) {
			if (count($ticketEquipment->getTicketSystems())>0) {

				return true;
			}
		}

		return false;
	}
	
	
	/**
	 * Return working minutes elapsed from OCCURED moment to RESOLVED moment.
	 */
	private function getWorkingTime($startDate, $endDate, $startWorkingTime = '07:30:00', $endWorkingTime = '16:30:00')
	{
		define ('ONEMINUTE', 60);
 
		// ESTABLISH THE MINUTES PER DAY FROM START AND END TIMES
		$startWorkingTime	= strtotime($startWorkingTime);
		$endWorkingTime		= strtotime($endWorkingTime);
		$minutes_per_day	= (int)( ($endWorkingTime - $startWorkingTime) / 60 )+1;
		
		// ESTABLISH THE HOLIDAYS
		$holidays = array(
			'2014-08-15',
			'2014-11-30',
			'2014-12-01',
			'2014-12-25',
			'2014-12-26',
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
		$start	=	$startDate->getTimestamp();
		$end	=	$endDate->getTimestamp();
			
		// RESET WORK MINUTES
		$workminutes = 0;
 
		// ITERATE OVER THE DAYS
		$start = $start - ONEMINUTE;
		while ($start < $end)
		{			
			$start = $start + ONEMINUTE;
				
			// ELIMINATE WEEKENDS - SAT AND SUN
			$weekday = date('D', $start);
			if (substr($weekday,0,1) == 'S') continue;
			
			// ELIMINATE HOLIDAYS
			$iso_date = date('Y-m-d', $start);
			if (in_array($iso_date, $holidays)) continue;
			
			// ELIMINATE HOURS BEFORE BUSINESS HOURS
			$daytime = date('H:i:s', $start);
			if(($daytime < date('H:i:s',$startWorkingTime))) continue;
			
			// ELIMINATE HOURS PAST BUSINESS HOURS
			$daytime = date('H:i:s', $start);
			if(($daytime > date('H:i:s',$endWorkingTime))) continue;
			
			$workminutes++;
		} // end while
		
		$workminutes = $workminutes-(ceil($workminutes/$minutes_per_day)>1 ? ceil($workminutes/$minutes_per_day)-1 : 1);
		
		return ($workminutes > 0 ? $workminutes : 0);
	}
}