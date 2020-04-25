<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserListCommand extends UserCommand
{
    protected static $defaultName = 'itk-dev:user:list';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->userManager->findUsersBy([]);

        $table = new Table($output);
        $table->setHeaders(['Username', 'Roles']);
        foreach ($users as $user) {
            $table->addRow([
                $user->getUsername(),
                implode(', ', $user->getRoles()),
            ]);
        }
        $table->render();

        return 0;
    }
}
