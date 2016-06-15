<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 4/27/2015
 * Time: 11:45 AM
 */

namespace Tlt\TicketBundle\Form\Type\Model;

class TicketFilters
{
    /**
     * @var array
     */
    protected $serviceType;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var bool
     */
    protected $isReal;

    /**
     * @return array
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @param array $serviceType
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * @return array
     */
    public function getIsReal()
    {
        return $this->isReal;
    }

    /**
     * @param array $isReal
     */
    public function setIsReal($isReal)
    {
        $this->isReal = $isReal;
    }
}