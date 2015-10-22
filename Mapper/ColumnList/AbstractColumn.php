<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class AbstractColumn
{
    abstract public function getType();

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => null,
            'template' => '@UploadData/Default/column.html.twig',
            'use_show' => false,
            'position' => 0,
        ));

        $resolver->setAllowedTypes(array(
            'position' => array('float', 'integer'),
            'use_show' => 'bool',
        ));
    }
} 