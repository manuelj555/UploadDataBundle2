<?php

namespace Manuelj555\Bundle\UploadDataBundle;

use Manuelj555\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterColumnListPass;
use Manuelj555\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterUploadConfigPass;
use Manuelj555\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterUploadReaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UploadDataBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterUploadConfigPass());
        $container->addCompilerPass(new RegisterColumnListPass());
        $container->addCompilerPass(new RegisterUploadReaderPass());
    }


}
