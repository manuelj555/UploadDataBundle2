<?php
/**
 * 29/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
//			dump($event->getData());
            $event->getForm()
                ->add('value', TextType::class, array(
                    'label' => $event->getData()->getFormLabel(),
//					'mapped' => false
                ));
        });
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute',
		));
	}

}