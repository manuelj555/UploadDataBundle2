<?php

namespace Manuel\Bundle\UploadDataBundle\Validator;

use Symfony\Component\Validator\ConstraintViolation;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnError extends ConstraintViolation
{
    private $groups = [];

    public function __construct($message, $propertyPath = null, array $messageParameters = [], $groups = 'default')
    {
        parent::__construct($message, $message, $messageParameters, null, $propertyPath, null, null, null);

        $this->groups = (array) $groups;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return bool
     */
    public function isDefaultGroup()
    {
        return in_array('default', $this->getGroups());
    }

    /**
     * @return bool
     */
    public function hasOtherGroups()
    {
        return 0 < count(array_diff($this->getGroups(), ['default']));
    }
}