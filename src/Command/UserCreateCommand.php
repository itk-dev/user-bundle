<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\Command;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UserCreateCommand extends UserCommand
{
    protected static $defaultName = 'itk-dev:user:create';

    protected function configure()
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('roles', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The roles')
            ->addOption('super', null, InputOption::VALUE_NONE, 'Instead of specifying roles, use this to quickly add the super administrator role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $roles = $input->getArgument('roles');
        $super = (true === $input->getOption('super'));

        if (!empty($roles) && $super) {
            throw new \InvalidArgumentException('You can pass either roles or the --super option (but not both simultaneously).');
        }

        if (empty($roles) && !$super) {
            throw new \RuntimeException('No roles specified.');
        }

        $user = $this->userManager->createUser($username);
        $this->userManager->setRoles($user, $roles);
        $this->userManager->updateUser($user);

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('username')) {
            $question = new Question('Please specify username: ');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new InvalidArgumentException('Username can not be empty');
                }
                $this->userManager->findUserByUsername($username, true);

                return $username;
            });
            $questions['username'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
