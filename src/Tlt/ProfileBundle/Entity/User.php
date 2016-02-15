<?php

namespace Tlt\ProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Department;

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
     * @ORM\Column(type="string", length=60)
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
     * @ORM\OrderBy({"name"="desc"})
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
     * @ORM\ManyToMany(targetEntity="Tlt\AdmnBundle\Entity\Owner")
     * @ORM\JoinTable(name="users_owners",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="owner_id", referencedColumnName="id")}
     *      )
     **/
    private $owners;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="Tlt\ProfileBundle\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $roluri;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $this->branches = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->roluri = new ArrayCollection();
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

    /**
     * Get departments
     *
     * @return array
     */
    public function getDepartmentsArray()
    {
        $departments = array();
        foreach ($this->departments as $department)
            $departments[$department->getId()] = $department->getName();

        if (empty($departments))
            return null;
        else
            return $departments;
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
        $this->password = $password;

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
    public function getRoluri()
    {
        return $this->roluri;
    }

    public function getRoles()
    {
        $roles = array();
        foreach ($this->roluri as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
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
     * @param Branch $branches
     * @return User
     */
    public function addBranch(Branch $branches)
    {
        $this->branches[] = $branches;

        return $this;
    }

    /**
     * Remove branches
     *
     * @param Branch $branches
     */
    public function removeBranch(Branch $branches)
    {
        $this->branches->removeElement($branches);
    }

    /**
     * Add department
     *
     * @param Department $department
     * @return User
     */
    public function addDepartment(Department $department)
    {
        $this->departments[] = $department;

        return $this;
    }

    /**
     * Remove departments
     *
     * @param Department $department
     */
    public function removeDepartment(Department $department)
    {
        $this->departments->removeElement($department);
    }

    /**
     * Add owner
     *
     * @param Owner $owner
     * @return User
     */
    public function addOwner(Owner $owner)
    {
        $this->owners[] = $owner;

        return $this;
    }

    /**
     * Remove owner
     *
     * @param Owner $owner
     */
    public function removeOwner(Owner $owner)
    {
        $this->owners->removeElement($owner);
    }

    /**
     * Get owners
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOwners()
    {
        return $this->owners;
    }

    public function getOwnersIds()
    {
        $ids = array();
        foreach ($this->owners as $owner)
            $ids[] = $owner->getId();

        if (empty($ids))
            return null;
        else
            return $ids;
    }

    /**
     * Add roluri
     *
     * @param Role $roluri
     * @return User
     */
    public function addRoluri(Role $roluri)
    {
        $this->roluri[] = $roluri;

        return $this;
    }

    /**
     * Remove roluri
     *
     * @param Role $roluri
     */
    public function removeRoluri(Role $roluri)
    {
        $this->roluri->removeElement($roluri);
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param  \DateTime $time [optional] New password request time. Null by default.
     *
     * @return User
     */
    public function setPasswordRequestedAt(\DateTime $time = null)
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
        && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken()
    {
        return base_convert(bin2hex(hash('sha256', uniqid(mt_rand(), true), true)), 16, 36);
    }

    /**
     * Set confirmation token.
     *
     * @param  string $token
     *
     * @return User
     */
    public function setConfirmationToken($token)
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param  string $password New password as plain string
     *
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Updates a user password if a plain password is set
     */
    public function updatePassword()
    {
        if (0 !== strlen($password = $this->getPlainPassword())) {
            $encoder = $this->getEncoder($user);

            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }
    }
}