<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Branch
 *
 * @ORM\Table(name="branches")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\BranchRepository")
 */
class Branch extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="Location", mappedBy="branch")
     */
    private $locations;
	

	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Branch
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
     * @return Branch
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
     * Add location
     *
     * @param \Tlt\AdmnBundle\Entity\Location $location
     * @return Branch
     */
    public function addLocation(\Tlt\AdmnBundle\Entity\Location $location)
    {
        $this->locations[] = $location;

        return $this;
    }

    /**
     * Remove location
     *
     * @param \Tlt\AdmnBundle\Entity\Location $location
     */
    public function removeLocation(\Tlt\AdmnBundle\Entity\Location $location)
    {
        $this->locations->removeElement($location);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }
	
	
	public function __toString()
	{
		return $this->getName();
	}
}
