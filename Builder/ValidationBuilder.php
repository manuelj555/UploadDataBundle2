<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Builder;

use InvalidArgumentException;
use Manuel\Bundle\UploadDataBundle\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function is_callable;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ValidationBuilder
{
    protected $config = array();

    protected $in;

    public function for($name, $groups = 'default')
    {
        return $this->with($name, $groups);
    }

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

    public function assertTrue($callback, $config = null)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('El argumento no es un callable');
        }

        $message = $config['message'] ?? 'Invalid Value';
        unset($config['message']);

        [$name, $groups] = $this->in;

        return $this->assertCallback(function ($value, ExecutionContextInterface $context)
        use ($callback, $message) {
            if (!$callback($value)) {
                $context->buildViolation($message)
                    ->setInvalidValue($value)
                    ->addViolation();
            }
        }, $config);
    }

    public function assertCallback($callback, $config = null)
    {
        $config['callback'] = $callback;

        return $this->addConstraint(new Callback($config));
    }

    public function addConstraint($constraint)
    {
        $this->verifyIn();

        [$name, $groups] = $this->in;

        foreach ($groups as $group) {
            $this->config[$group][$name][] = $constraint;
        }

        return $this;
    }

    protected function verifyIn()
    {
        if (null == $this->in) {
            throw new \LogicException(sprintf('No puede agregar una regla de validación sin antes llamar al método "->with()"'));
        }
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
        return $this->addConstraint(new Blank($config));
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

    public function assertEntityExist($class, $property, $config = [])
    {
        $config = [
                'class' => $class,
                'property' => $property,
            ] + $config;

        return $this->addConstraint(new EntityExists($config));
    }
} 