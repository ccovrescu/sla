<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceToSystems
 *
 * @ORM\Table(name="service_to_systems")
 * @ORM\Entity()
 */
class ServiceToSystem extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="serviceToSystems")
	 * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity="System", inversedBy="serviceToSystems")
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
     * Set service
     *
     * @param \Tlt\AdmnBundle\Entity\Service $service
     * @return ServiceToSystems
     */
    public function setService(\Tlt\AdmnBundle\Entity\Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \Tlt\AdmnBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
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