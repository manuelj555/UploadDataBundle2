<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Builder;

use Manuel\Bundle\UploadDataBundle\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ValidationBuilder
{
    protected $config = array();

    protected $in;

    public function with($name, $groups = 'default')
    {
        $groups = (array)$groups;

        foreach ($groups as $group) {
            if (!isset($this->config[$group][$name])) {
                $this->config[$group][$name] = [];
            }
        }

        $this->in = [$name, $groups];

        return $this;
    }

    protected function verifyIn()
    {
        if (null == $this->in) {
            throw new \LogicException(sprintf('No puede agregar una regla de validación sin antes llamar al método "->with()"'));
        }
    }

    public function addConstraint($constraint)
    {
        $this->verifyIn();

        list($name, $groups) = $this->in;

        foreach ($groups as $group){
            $this->config[$group][$name][] = $constraint;
        }

        return $this;
    }

    public function assertNotNull($config = null)
    {
        return $this->addConstraint(new NotNull($config));
    }

    public function assertNotBlank($config = null)
    {
        return $this->addConstraint(new NotBlank($config));
    }

    public function assertBlank($config = null)
    {
        return $this->addConstraint(new NotBlank($config));
    }

    public function assertCallback($callback, $config = null)
    {
        $config['callback'] = $callback;

        return $this->addConstraint(new Callback($config));
    }

    public function assertDate($config = null)
    {
        return $this->addConstraint(new Date($config));
    }

    public function assertDatetime($config = null)
    {
        return $this->addConstraint(new DateTime($config));
    }

    public function assertEmail($config = null)
    {
        return $this->addConstraint(new Email($config));
    }

    public function assertType($type, $config = null)
    {
        return $this->addConstraint(new Type(array('type' => $type) + (array)$config));
    }

    public function end()
    {
        $this->in = null;

        return $this;
    }

    public function getValidations()
    {
        return $this->config;
    }

    public function assertEntityExist($class, $property, $config)
    {
        $config = [
                'class' => $class,
                'property' => $property,
            ] + $config;

        return $this->addConstraint(new EntityExists($config));
    }
} 