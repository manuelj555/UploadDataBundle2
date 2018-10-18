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
class AttributeColumn extends AbstractColumn
{

    public function getType()
    {
        return 'attribute';
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'template' => '@UploadData/Default/column_attribute.html.twig',
            'position' => 40,
            'align' => 'left',
        ));
    }
}
