<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper\ColumnList;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ActionColumn extends AbstractColumn
{

    public function getType()
    {
        return 'action';
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'template' => '@UploadData/Default/column_action.html.twig',
            'use_show' => true,
            'modal' => false,
            'confirm' => function (Options $options) {
                return isset($options['confirm_text']) and $options['confirm_text'];
            },
            'confirm_text' => false,
            'position' => 301,
            'url' => null,
        ));

        $resolver->setRequired(array(
            'condition'
        ));

        $resolver->setDefined(array('modal_route', 'action_name'));

        $resolver->setAllowedTypes('condition',['Closure', 'callable']);
        $resolver->setAllowedTypes('url',['Closure', 'callable']);
        $resolver->setAllowedTypes('action_name','string');
    }
}
