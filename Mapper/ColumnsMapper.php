<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Mapper;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnsMapper
{
    protected $columns = array();
    protected $labels = array();

    public function add($name, $type = null, array $options = array())
    {

        $this->columns[$name] = array(
            'type' => $type,
            'options' => $options,
        );

        $this->labels[$name] = isset($options['label']) ? $options['label'] : $name;

        return $this;
    }

    public function getNames()
    {
        return array_keys($this->columns);
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function getLabel($name)
    {
        return isset($this->labels[$name]) ? $this->labels[$name] : null;
    }
}