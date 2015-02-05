<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\ColumnFactory;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ListMapper
{
    protected $columns = array();
    /**
     * @var ColumnFactory
     */
    protected $columnListFactory;

    function __construct($columnListFactory)
    {
        $this->columnListFactory = $columnListFactory;
    }

    public function add($name, $type = null, array $options = array())
    {

        $item = $this->columnListFactory->create($name, $type, $options);

        $this->columns[$name] = $item;

        return $this;
    }

    public function addAction($name, array $options = array())
    {

        $item = $this->columnListFactory->create($name, 'action', $options);

        $this->columns[$name] = $item;

        return $this;
    }

    public function remove($name)
    {
        unset($this->columns[$name]);

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }


}