<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\User;

use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;

interface UserInterface extends CoreUserInterface
{
    public function getLastLoggedInAt(): ?DateTimeInterface;

    public function setLastLoggedInAt(DateTimeInterface $lastLoggedInAt): self;
}
