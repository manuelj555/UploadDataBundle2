<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class LoadedColumn
{
    protected $name;
    protected $type;
    protected $options = array();
    protected $template;

    function __construct($name, $type, $options = array())
    {
        $this->name = $name;
        $this->options = $options;
        $this->type = $type;
        $this->setTemplate($options['template']);
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function callOption($name, $arguments)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('The Option "%s" does not exists', $name));
        }

        if (!is_callable($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('The Option "%s" is not callable', $name));
        }

        return call_user_func_array($this->options[$name], (array) $arguments);
    }

} 