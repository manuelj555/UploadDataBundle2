<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Countable;
use InvalidArgumentException;
use Manuel\Bundle\UploadDataBundle\Validator\Constraint\EntityExists;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function count;
use function dd;
use function is_callable;
use function sprintf;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
final class ConfigColumns implements Countable
{
    private array $columns = [];
    private array $labels = [];

    private ?string $currentColumn = null;
    private ?array $validatingGroups = null;
    protected array $validations = [];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function add($name, array $options = []): self
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'aliases' => [],
            'label' => $name,
            'name' => $name,
            'required' => true,
            'similar' => function (Options $options) {
                return count($options['aliases']) > 0;
            },
            'formatter' => function ($value) {
                return $value;
            },
        ]);
        $resolver->setNormalizer('aliases', function (Options $options, $value) {
            $value[] = $options['label'];
            $value[] = $options['name'];

            return array_map(function ($alias) {
                return strtolower($alias);
            }, $value);
        }
        );

        $options = $resolver->resolve($options);

        $this->columns[$name] = $options;
        $this->labels[$name] = $options['label'];
        $this->currentColumn = $name;
        $this->validatingGroups = null;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumnsWithLabels(): array
    {
        $columns = [];

        foreach ($this->columns as $key => $config) {
            $columns[$key] = $config['label'];
        }

        return $columns;
    }

    public function getColumnNames(): array
    {
        return array_keys($this->columns);
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getLabel(string $name): ?string
    {
        return $this->labels[$name] ?? null;
    }

    public function isRequired(string $columnName): bool
    {
        return $this->columns[$columnName]['required'] ?? false;
    }

    public function count()
    {
        return count($this->getColumns());
    }

    public function validate(): self
    {
        if (null == $this->currentColumn) {
            throw new \LogicException(sprintf(
                'No puede llamar "->validate()" sin antes llamar a "->add(columnName)"'
            ));
        }

        $this->validatingGroups = ['default'];

        return $this;
    }

    public function forGroups(array|string $groups): self
    {
        if (null === $this->validatingGroups) {
            throw new \LogicException(sprintf(
                'No puede llamar "->forGroups()" sin antes llamar a "->validate()"'
            ));
        }

        $this->validatingGroups = (array)$groups;

        return $this;
    }

    public function constraint(Constraint $constraint): self
    {
        $this->verifyAddConstraint();

        foreach ($this->validatingGroups as $group) {
            $this->validations[$group][$this->currentColumn][] = $constraint;
        }

        return $this;
    }

    public function assertCallback(callable $callback, array $config = []): self
    {
        $config['callback'] = $callback;

        return $this->constraint(new Callback($config));
    }

    public function assertTrue(callable $callback, array $config = []): self
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('El argumento no es un callable');
        }

        $message = $config['message'] ?? 'Invalid Value';
        unset($config['message']);

        return $this->assertCallback(static function ($value, ExecutionContextInterface $context)
        use ($callback, $message) {
            if (!$callback($value)) {
                $context->buildViolation($message)
                    ->setInvalidValue($value)
                    ->addViolation();
            }
        }, $config);
    }

    public function assertNotNull(array $config = []): self
    {
        return $this->constraint(new NotNull($config));
    }

    public function assertNotBlank(array $config = []): self
    {
        return $this->constraint(new NotBlank($config));
    }

    public function assertBlank(array $config = []): self
    {
        return $this->constraint(new Blank($config));
    }

    public function assertDate(array $config = []): self
    {
        return $this->constraint(new Date($config));
    }

    public function assertDatetime(array $config = []): self
    {
        return $this->constraint(new DateTime($config));
    }

    public function assertEmail(array $config = []): self
    {
        return $this->constraint(new Email($config));
    }

    public function assertType($type, array $config = []): self
    {
        return $this->constraint(new Type(['type' => $type] + $config));
    }

    public function assertEntityExist(string $class, string $property, array $config = []): self
    {
        $config = [
                'class' => $class,
                'property' => $property,
            ] + $config;

        return $this->constraint(new EntityExists($config));
    }

    public function endValidate(): self
    {
        $this->currentColumn = null;
        $this->validatingGroups = null;

        return $this;
    }

    public function getValidations(): array
    {
        return $this->validations;
    }

    private function verifyAddConstraint(): void
    {
        if (null === $this->currentColumn || null === $this->validatingGroups) {
            throw new \LogicException(sprintf(
                'No puede agregar una regla de validación sin antes llamar al método "->validate()"'
            ));
        }
    }
}
