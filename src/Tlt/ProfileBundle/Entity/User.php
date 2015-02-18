<?php

namespace Tlt\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Tlt\ProfileBundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

   /**
     * @ORM\ManyToMany(targetEntity="Tlt\AdmnBundle\Entity\Branch")
     * @ORM\JoinTable(name="users_branches",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="branch_id", referencedColumnName="id")}
     *      )
     **/
	private $branches;

   /**
     * @ORM\ManyToMany(targetEntity="Tlt\AdmnBundle\Entity\Department")
     * @ORM\JoinTable(name="users_departments",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="department_id", referencedColumnName="id")}
     *      )
     **/
	private $departments;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;

	
    public function __construct()
    {
        $this->isActive		= true;
        $this->salt			= md5(uniqid(null, true));
		$this->branches		= new \Doctrine\Common\Collections\ArrayCollection();
		$this->departments	= new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get branches
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBranches()
    {
        return $this->branches;
    }
	
    /**
     * Get departments
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

	public function getBranchesIds()
	{
		$ids = array();
		foreach ($this->branches as $branch)
			$ids[] = $branch->getId();
		
		if( empty($ids) )
			return null;
		else
			return $ids;
	}
	
	public function getDepartmentsIds()
	{
		$ids = array();
		foreach ($this->departments as $department)
			$ids[] = $department->getId();
		
		if( empty($ids) )
			return null;
		else
			return $ids;
	}	
	

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
		$roles = array();
		foreach ($this->roles as $role)
		{
			$roles[]	=	$role->getRole();
		}
		// var_dump($roles);die();
		return $roles;
        // return (($this->username == 'admin' || $this->username == 'mihaela' || $this->username || 'radu') ? array('ROLE_ADMIN') : array('ROLE_USER'));
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
        ) = unserialize($serialized);
    }
}
