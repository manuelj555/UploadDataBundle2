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
class NumberLinkColumn extends AbstractColumn
{

    public function getType()
    {
        return 'number_link';
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'route' => null,
            'url' => null,
            'attr' => array(),
            'parameters' => array(),
            'template' => '@UploadData/Default/column_number_link.html.twig',
        ));

        $resolver->setDefined(array('condition'));
        $resolver->setAllowedTypes('condition',['Closure', 'callable']);
    }


}
