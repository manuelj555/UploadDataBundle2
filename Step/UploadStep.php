<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Step;

use Symfony\Component\HttpFoundation\Request;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadStep
{
    protected $label;
    protected $options;

    function __construct($label, $options)
    {
        $this->label = $label;
        $this->options = $options;
    }


    public function getLabel()
    {

    }

    public function process(Request $request)
    {

    }

    public function getConfig()
    {

    }
} 