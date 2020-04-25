<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\Command;

use ItkDev\UserBundle\Util\UserManager;
use Symfony\Component\Console\Command\Command;

abstract class UserCommand extends Command
{
    /** @var UserManager */
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }
}
