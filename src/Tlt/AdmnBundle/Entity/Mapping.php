<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tlt\TicketBundle\Entity\TicketMapping;

/**
 * Mapping
 *
 * @ORM\Table(name="mappings")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\MappingRepository")
 */
class Mapping extends AbstractEntity
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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Equipment")
	 * @ORM\JoinColumn(name="equipment", referencedColumnName="id")
     */
    private $equipment;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="System", inversedBy="mappings")
	 * @ORM\JoinColumn(name="system", referencedColumnName="id")
     * @Assert\NotBlank(
     *     message = "Trebuie sa completati o valoare. Campul este obligatoriu."
     * )
     */
    private $system;
     /**
      * @ORM\OneToMany(targetEntity="Tlt\TicketBundle\Entity\TicketMapping", mappedBy="mapping", cascade={"persist", "remove"})
     */
    protected $ticketMapping;

    /**
     * Set id
     *
     * @param integer $id
     * @return Service
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
     * Set equipment
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipment
     * @return Mapping
     */
    public function setEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \Tlt\AdmnBundle\Entity\Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set system
     *
     * @param \Tlt\AdmnBundle\Entity\System $system
     * @return Mapping
     */
    public function setSystem(\Tlt\AdmnBundle\Entity\System $system = null)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return \Tlt\AdmnBundle\Entity\System
     */
    public function getSystem()
    {
        return $this->system;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketMapping = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add ticketMapping
     *
     * @param TicketMapping $ticketMapping
     * @return Mapping
     */
    public function addTicketMapping(TicketMapping $ticketMapping)
    {
        $this->ticketMapping[] = $ticketMapping;

        return $this;
    }

    /**
     * Remove ticketMapping
     *
     * @param TicketMapping $ticketMapping
     */
    public function removeTicketMapping(TicketMapping $ticketMapping)
    {
        $this->ticketMapping->removeElement($ticketMapping);
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
