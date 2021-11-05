<?php
/**
 * Optime Consulting.
 * User: MAGUIRRE
 * Date: 9/03/2018
 * Time: 10:33 AM
 */

namespace Manuel\Bundle\UploadDataBundle\Validator;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use function join;

/**
 * Class GroupedConstraintViolations
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class GroupedConstraintViolations implements \Countable, \IteratorAggregate
{
    /**
     * @var array|ConstraintViolationListInterface[]
     */
    private $violations = [];

    /**
     * GroupedConstraintViolations constructor.
     *
     * @param array|ConstraintViolationListInterface[] $violations
     */
    public function __construct(array $violations = [])
    {
        $this->violations = $violations;
    }

    public static function fromArray(array $violations)
    {
        if (count($violations)) {
            // verificamos si los errores vienen en formato antiguo:
            $keys = array_keys($violations);
            if (isset($violations[$keys[0]][0])) {
                // Si no vienen agrupados por categorias de errores, los agregamos a una por defecto.
                $violations = ['default' => $violations];
            }
        }

        return new static($violations);
    }

    public function add($group, ConstraintViolationInterface $violation)
    {
        if (!isset($this->violations[$group][$violation->getPropertyPath()])) {
            $this->violations[$group][$violation->getPropertyPath()] = [];
        }

        $this->violations[$group][$violation->getPropertyPath()][] = $violation->getMessage();
    }

    public function addColumnError(ColumnError $violation)
    {
        if ($violation->hasOtherGroups()) {
            foreach ($violation->getGroups() as $group) {
                $this->add($group, $violation);
            }
        } else {
            $this->add('default', $violation);
        }
    }

    public function addAll($group, $violations)
    {
        foreach ($violations as $violation) {
            if ($violation instanceof ColumnError) {
                $this->addColumnError($group, $violation);
            } else {
                $this->add($group, $violation);
            }
        }
    }

    public function getAll($group = null, $grouped = true)
    {
        $errors = [];

        if (null !== $group) {
            return $this->getByGroup($group);
        }

        if ($grouped) {
            return $this->violations;
        }

        foreach ($this->violations as $groupName => $violationList) {
            foreach ($violationList as $propertyPath => $violations) {
                if (!isset($errors[$propertyPath])) {
                    $errors[$propertyPath] = $violations;
                } else {
                    $errors[$propertyPath] = array_unique(
                        array_merge($errors[$propertyPath], $violations)
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * @param string $name
     * @return ConstraintViolationListInterface
     */
    public function getByGroup($name)
    {
        return isset($this->violations[$name]) ? $this->violations[$name] : [];
    }

    public function getGroups()
    {
        return array_keys($this->violations);
    }

    public function hasOtherGroups()
    {
        $groups = $this->getGroups();

        return 0 < count(array_diff($groups, ['default']));
    }

    public function hasViolationsInOtherGroups()
    {
        $violations = $this->violations;
        unset($violations['default']);

        return 0 < count(array_filter($violations, function ($errors) {
                return count($errors) > 0;
            }));
    }

    public function hasViolationsForGroup($name)
    {
        return isset($this->violations[$name]) && 0 < count($this->violations[$name]);
    }

    public function count()
    {
        return count($this->getAll('default'));
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getAll('default', false));
    }

    public function getErrorsAsSimpleFormat(string $separator = ', '):array
    {
        $errors = [];

        foreach ($this->getAll(null, false) as $property => $propertyErrors) {
            $errors[$property] = join($separator, $propertyErrors);
        }

        return $errors;
    }
}