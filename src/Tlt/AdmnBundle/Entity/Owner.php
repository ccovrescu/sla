<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Owner
 *
 * @ORM\Table(name="owners")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\OwnerRepository")
 */
class Owner extends AbstractEntity
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="owner")
     */
    private $equipments;

    /**
     * @var string
     *
     * @ORM\Column(name="reports_owner", type="string", length=64)
     */
    private $reportsOwner;


	/**
     * Constructor
     */
    public function __construct()
    {
        $this->equipments = new ArrayCollection();
    }

	
    /**
     * Set id
     *
     * @param integer $id
     * @return Owner
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
     * Set name
     *
     * @param string $name
     * @return Owner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
	
	
	/**
     * Add equipment
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipment
     * @return Owner
     */
    public function addEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment)
    {
		$equipment->setOwner( $this );
        $this->equipments[] = $equipment;

        return $this;
    }

    /**
     * Remove equipment
     *
     * @param \Tlt\AdmnBundle\Entity\Equipment $equipment
     */
    public function removeEquipment(\Tlt\AdmnBundle\Entity\Equipment $equipment)
    {
        $this->equipments->removeElement($equipment);
    }

    /**
     * Get equipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipments()
    {
        return $this->equipments;
    }

    /**
     * @return string
     */
    public function getReportsOwner()
    {
        return $this->reportsOwner;
    }

    /**
     * @param string $reportsOwner
     */
    public function setReportsOwner($reportsOwner)
    {
        $this->reportsOwner = $reportsOwner;
    }

	public function __toString()
	{
		return $this->getName();
	}
}
