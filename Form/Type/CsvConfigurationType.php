<?php
/**
 * 29/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Form\Type;

use Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CsvConfigurationType extends AbstractType
{

    public function getName()
    {
        return 'csv_configuration';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attributes', 'collection', array(
            'type' => new AttributeType(),
            'label' => false,
            'options' => array(
                'label' => false,
            ),
        ));

        $builder->add('enviar', 'submit', array(
            'attr' => array(
                'class' => 'btn-primary',
            ),
            'label' => 'label.next_step',
            'translation_domain' => 'upload_data',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Manuel\Bundle\UploadDataBundle\Entity\Upload',
        ));
    }


}