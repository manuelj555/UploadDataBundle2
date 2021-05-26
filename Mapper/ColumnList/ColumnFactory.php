<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;

use Psr\Container\ContainerInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create($name, $type = null, $options = array())
    {
        $type = $type ? : 'text';

        if (!$this->container->has($type)) {
            throw new InvalidArgumentException(sprintf('Column List Type "%s" does not exist', $type));
        }

        $type = $this->container->get($type);

        $resolver = new OptionsResolver();
        $type->setDefaultOptions($resolver);
        $options = $resolver->resolve($options);

        $column = new LoadedColumn($name, $type, $options);

        return $column;
    }

} 