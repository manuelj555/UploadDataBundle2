<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Config\ReaderSteps;

use Manuelj555\Bundle\UploadDataBundle\Step\UploadStep;

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