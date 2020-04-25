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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UserPasswordCommand extends UserCommand
{
    protected static $defaultName = 'itk-dev:user:password';

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            new InputArgument('password', InputArgument::REQUIRED, 'The password'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $user = $this->userManager->findUserByUsername($username, true);
        if (empty($password)) {
            throw new InvalidArgumentException('Password can not be empty');
        }
        $this->userManager->setPassword($user, $password);
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

        if (!$input->getArgument('password')) {
            $question = new Question('Please specify password: ');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new InvalidArgumentException('Password can not be empty');
                }

                return $password;
            });
            $questions['password'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
