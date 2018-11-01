<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SystemCategory
 *
 * @ORM\Table(name="system_category")
 * @ORM\Entity(repositoryClass="Tlt\AdmnBundle\Entity\SystemCategoryRepository")
 */
class SystemCategory extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="categories")
     * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */
    private $department;


//    /**
//     * @var integer
//     *
//     * @ORM\Column(name="department", type="integer")
//     */
  //  private $department;



    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Tlt\AdmnBundle\Entity\System", mappedBy="category")
     */
    private $systems;

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
     * Set department
     *
     * @param integer $department
     * @return SystemCategory
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return integer 
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SystemCategory
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
     * Constructor
     */
    public function __construct()
    {
        $this->systems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add systems
     *
     * @param \Tlt\AdmnBundle\Entity\System $systems
     * @return SystemCategory
     */
    public function addSystem(\Tlt\AdmnBundle\Entity\System $systems)
    {
        $this->systems[] = $systems;

        return $this;
    }

    /**
     * Remove systems
     *
     * @param \Tlt\AdmnBundle\Entity\System $systems
     */
    public function removeSystem(\Tlt\AdmnBundle\Entity\System $systems)
    {
        $this->systems->removeElement($systems);
    }

    /**
     * Get systems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSystems()
    {
        return $this->systems;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getDepartmentName()
    {

    }
}
