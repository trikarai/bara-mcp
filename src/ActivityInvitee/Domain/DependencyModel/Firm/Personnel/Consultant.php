<?php

namespace ActivityInvitee\Domain\DependencyModel\Firm\Personnel;

use ActivityInvitee\Domain\DependencyModel\Firm\Personnel;

class Consultant
{

    /**
     *
     * @var Personnel
     */
    protected $personnel;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var bool
     */
    protected $removed;

    protected function __construct()
    {
        
    }

}
