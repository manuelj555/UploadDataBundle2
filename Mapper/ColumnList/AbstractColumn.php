<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;

use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class AbstractColumn
{
    abstract public function getType();

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label' => null,
            'template' => '@UploadData/Default/column.html.twig',
            'use_show' => false,
            'position' => 0,
        ));

        $resolver->setAllowedTypes('position',['float', 'integer']);
        $resolver->setAllowedTypes('use_show','bool');
    }
} 
