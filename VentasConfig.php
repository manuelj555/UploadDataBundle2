<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle;

use Manuelj555\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class VentasConfig extends UploadConfig
{

    public function configureColumns(ColumnsMapper $mapper)
    {
        $mapper
            ->add('name', array(
                'aliases' => array('AAA', 'BBB', 'Name'),
                'similar' => true,
            ))
            ->add('email')
            ->add('years');
    }

    public function configureValidations(ValidationBuilder $builder)
    {
        $builder
            ->with('name')
            ->assertNotBlank()
            ->end()
            ->with('email')
            ->assertNotBlank()
            ->assertEmail()
            ->end()
            ->with('years')
            ->assertNotBlank()
            ->assertType('numeric')
            ->end();
    }


} 