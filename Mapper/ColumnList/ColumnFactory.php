<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;

use Psr\Log\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnFactory
{
    protected $types = array();
    protected $container;

    function __construct($container, $types)
    {
        $this->container = $container;
        $this->types = $types;
    }

    public function create($name, $type = null, $options = array())
    {
        $type = $type ? : 'text';

        if (!isset($this->types[$type])) {
            throw new InvalidArgumentException(sprintf('Column List Type "%s" does not exist', $type));
        }

        $type = $this->container->get($this->types[$type]);

        $resolver = new OptionsResolver();
        $type->setDefaultOptions($resolver);
        $options = $resolver->resolve($options);

        $column = new LoadedColumn($name, $type, $options);

        return $column;
    }

} 