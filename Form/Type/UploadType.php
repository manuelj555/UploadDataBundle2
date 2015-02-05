<?php
/**
 * 16/10/2014
 * tracking_upload
 */

namespace Manuel\Bundle\UploadDataBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadType extends AbstractType
{

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'upload_data_type';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array(
            'label' => 'label.file',
        ));
    }


}