<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Config\ReaderSteps;

use Manuel\Bundle\UploadDataBundle\Step\UploadStep;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CsvSteps
{
    protected $steps;

    public function a()
    {
        new UploadStep('Opciones', null);
    }
} 