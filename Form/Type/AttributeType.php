<?php
/**
 * 29/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class AttributeType extends AbstractType
{

    public function getName()
    {
        return 'upload_attribute';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->add('value', 'text', array());

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $event->getForm()
                ->add('value', 'text', array(
                    'label' => $event->getData()->getFormLabel(),
                ));
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Manuelj555\Bundle\UploadDataBundle\Entity\UploadAttribute',
        ));
    }

//    public function buildView(FormView $view, FormInterface $form, array $options)
//    {
//        parent::buildView($view, $form, $options);
//
////        var_dump($form);
//
//        if($form->getData()){
//            $view->vars['label'] = $form->getData()->getFormLabel();
//        }
//    }
}