<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class VentasConfig extends UploadConfig
{

    public function configureColumns(ColumnsMapper $mapper)
    {
        $mapper->add('name')
            ->add('email')
            ->add('years');
    }

} 