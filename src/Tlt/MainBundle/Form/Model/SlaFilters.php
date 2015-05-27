<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 4/27/2015
 * Time: 11:45 AM
 */

namespace Tlt\MainBundle\Form\Model;

class SlaFilters extends JournalFilters
{
    /**
     * @var integer
     *
     */
    protected $is_closed;

    /**
     * @return int
     */
    public function getIsClosed()
    {
        return $this->is_closed;
    }

    /**
     * @param int $is_closed
     */
    public function setIsClosed($is_closed)
    {
        $this->is_closed = $is_closed;
    }
}