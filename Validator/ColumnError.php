<?php

namespace Manuel\Bundle\UploadDataBundle\Validator;

use Symfony\Component\Validator\ConstraintViolation;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnError extends ConstraintViolation
{

    public function __construct($message, $propertyPath = null, array $messageParameters = array())
    {
        parent::__construct($message, $message, $messageParameters, null, $propertyPath, null, null, null);
    }
}