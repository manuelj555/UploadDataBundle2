<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnList;

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
        ));
    }
} 