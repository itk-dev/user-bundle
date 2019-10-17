<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle;

use ItkDev\UserBundle\DependencyInjection\ItkDevUserBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ItkDevUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ItkDevUserBundleExtension();
    }
}
