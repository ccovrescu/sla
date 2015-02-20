<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks() 
 */
abstract class AbstractEntity
{
	/**
	 * @var \datetime
     *
     * @ORM\Column(name="inserted_at", type="datetime")
	 */
	private $insertedAt;	
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="inserted_by", type="string")
	 */
	private $insertedBy;
	
	/**
	 * @var \datetime
     *
     * @ORM\Column(name="modified_at", type="datetime")
	 */
	private $modifiedAt;	
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="modified_by", type="string")
	 */
	private $modifiedBy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="from_host", type="string")
	 */
	private $fromHost;

	
    /**
     * @ORM\PrePersist
     */
    public function setinsertedAtValue()
    {
        $this->insertedAt = new \DateTime("now");
    }
	
	public function getInsertedAt()
	{
		return $this->insertedAt;
	}	
	
	public function setInsertedBy($insertedBy)
	{
		$this->insertedBy	=	$insertedBy;
	}
	
	public function getInsertedBy()
	{
		return $this->insertedBy;
	}
	
    /**
	 * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setModifiedAtValue()
    {
        $this->modifiedAt = new \DateTime("now");
    }

	public function getModifiedAt()
	{
		return $this->insertedAt;
	}	
	
	public function setModifiedBy($modifiedBy)
	{
		$this->modifiedBy	=	$modifiedBy;
	}
	
	public function getModifiedBy()
	{
		return $this->modifiedBy;
	}
	
    public function setFromHost($fromHost)
    {
        $this->fromHost = $fromHost;
    }

	public function getFromHost()
	{
		return $this->fromHost;
	}	
}