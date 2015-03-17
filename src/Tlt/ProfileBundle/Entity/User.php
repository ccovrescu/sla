<?php

namespace Tlt\ProfileBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $compartment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailNotification;


    /**
     * @ORM\ManyToMany(targetEntity="Tlt\AdmnBundle\Entity\Branch")
     * @ORM\JoinTable(name="users_branches",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="branch_id", referencedColumnName="id", onDelete="CASCADE")}
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
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     */
    private $roles;


    public function __construct()
    {
        $this->salt = md5(uniqid(null, true));
        $this->branches = new \Doctrine\Common\Collections\ArrayCollection();
        $this->departments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
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

        if (empty($ids))
            return null;
        else
            return $ids;
    }

    public function getDepartmentsIds()
    {
        $ids = array();
        foreach ($this->departments as $department)
            $ids[] = $department->getId();

        if (empty($ids))
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
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = sha1($password);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get emailNotification
     *
     * @return boolean
     */
    public function getEmailNotification()
    {
        return $this->emailNotification;
    }

    /**
     * Set emailNotification
     *
     * @param boolean $emailNotification
     * @return User
     */
    public function setEmailNotification($emailNotification)
    {
        $this->emailNotification = $emailNotification;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }
        // var_dump($roles);die();
        return $roles;
        // return (($this->username == 'admin' || $this->username == 'mihaela' || $this->username || 'radu') ? array('ROLE_ADMIN') : array('ROLE_USER'));
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
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

    /**
     * Set compartment
     *
     * @param string $compartment
     * @return User
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
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Add branches
     *
     * @param \Tlt\AdmnBundle\Entity\Branch $branches
     * @return User
     */
    public function addBranch(\Tlt\AdmnBundle\Entity\Branch $branches)
    {
        $this->branches[] = $branches;

        return $this;
    }

    /**
     * Remove branches
     *
     * @param \Tlt\AdmnBundle\Entity\Branch $branches
     */
    public function removeBranch(\Tlt\AdmnBundle\Entity\Branch $branches)
    {
        $this->branches->removeElement($branches);
    }

    /**
     * Add departments
     *
     * @param \Tlt\AdmnBundle\Entity\Department $departments
     * @return User
     */
    public function addDepartment(\Tlt\AdmnBundle\Entity\Department $departments)
    {
        $this->departments[] = $departments;

        return $this;
    }

    /**
     * Remove departments
     *
     * @param \Tlt\AdmnBundle\Entity\Department $departments
     */
    public function removeDepartment(\Tlt\AdmnBundle\Entity\Department $departments)
    {
        $this->departments->removeElement($departments);
    }

    /**
     * Add roles
     *
     * @param \Tlt\ProfileBundle\Entity\Role $roles
     * @return User
     */
    public function addRole(\Tlt\ProfileBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \Tlt\ProfileBundle\Entity\Role $roles
     */
    public function removeRole(\Tlt\ProfileBundle\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }
}
