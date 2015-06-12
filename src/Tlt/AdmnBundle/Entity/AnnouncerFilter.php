<?php

namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tlt\AdmnBundle\Entity\Branch;

class AnnouncerFilter
{
    /**
     * @var Branch
     */
    private $branch;

    /**
     * Get branch
     *
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set branch
     * @param Branch $branch
     *
     * @return Filter
     */
    public function setBranch($branch)
    {
        $this->branch	=	$branch;

        return $this;
    }
}