<?php

namespace Manuel\Bundle\UploadDataBundle;

use Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterColumnListPass;
use Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterUploadConfigPass;
use Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler\RegisterUploadReaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UploadDataBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterUploadConfigPass());
        $container->addCompilerPass(new RegisterUploadReaderPass());
    }


}
