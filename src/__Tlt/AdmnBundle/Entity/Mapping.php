<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $system;


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
     * @param \Tlt\AdmneBundle\Entity\Equipment $equipment
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
     * @param \Tlt\AdmneBundle\Entity\System $system
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
}
