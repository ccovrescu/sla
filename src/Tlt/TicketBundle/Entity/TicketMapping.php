<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/19/2015
 * Time: 11:22 AM
 */

namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Tlt\AdmnBundle\Entity\Mapping;

/**
 * TicketMapping
 *
 * @ORM\Table(name="tickets_ticket_mapping")
 * @ORM\Entity()
 */
class TicketMapping
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
     *
     * @ORM\ManyToOne(targetEntity="Ticket", inversedBy="ticketMapping")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    private $ticket;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Tlt\AdmnBundle\Entity\Mapping", inversedBy="ticketMapping")
     * @ORM\JoinColumn(name="mapping_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $mapping;

    /**
     * @var integer
     *
     * @ORM\Column(name="resolved_in", type="integer", nullable=true)
     */
    private $resolvedIn;

    /**
     * @var boolean
     *
     * @ORM\Column(name="total_affected", type="boolean", nullable=true)
     */
    private $totalaffected;

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
     * Set ticket
     *
     * @param Ticket $ticket
     * @return TicketMapping
     */
    public function setTicket(Ticket $ticket = null)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set mapping
     *
     * @param Mapping $mapping
     * @return TicketMapping
     */
    public function setMapping(Mapping $mapping = null)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * Get mapping
     *
     * @return Mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Set resolvedIn
     *
     * @param integer $resolvedIn
     * @return TicketMapping
     */
    public function setResolvedIn($resolvedIn)
    {
        $this->resolvedIn = $resolvedIn;

        return $this;
    }

    /**
     * Get resolvedIn
     *
     * @return integer 
     */
    public function getResolvedIn()
    {
        return $this->resolvedIn;
    }


    /**
     * Set totalaffected
     *
     * @param boolean $totalaffected
     * @return TicketMapping
     */
    public function setTotalaffected($totalaffected)
    {
        $this->totalaffected = $totalaffected;

        return $this;
    }
    /**
     * Get totalaffected
     *
     * @return boolean
     */
    public function isTotalaffected()
    {
        return $this->totalaffected;
    }
    public function __toString()
    {
        return (string)$this->id;
    }

}
