<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

/**
 * Trait DebuggingExceptionProcessTrait
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
trait DebuggingExceptionProcessTrait
{
    protected function addUploadExceptionLog(\Exception $e)
    {
        if ($this->container->has('logger')) {
            $this->get('logger')->critical('No se pudo procesar la lectura del excel', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ));
        }

        if ($this->container->getParameter('kernel.environment') !== 'dev') {
            return;
        }

        if (!$this->container->has('session')) {
            return;
        }

        if (!$this->container->has('security.authorization_checker')) {
            return;
        }

        $configuredRole = $this->container->getParameter('upload_data.secuity.debugging_role');

        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
            && !$this->container->get('security.authorization_checker')->isGranted($configuredRole)
        ) {
            return;
        }

        $this->container->get('session')->getFlashBag()->add('error', $e->getMessage());
    }
}