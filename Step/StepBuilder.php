<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Step;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class StepBuilder
{
    protected $steps = array();

    public function add($label, $options)
    {
        $this->steps[$label] = new UploadStep($label, $options);

        return $this;
    }
} 