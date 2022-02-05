<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Form\Type;

use Manuel\Bundle\UploadDataBundle\Data\ColumnsMatchInfo;
use Manuel\Bundle\UploadDataBundle\Data\Exception\InvalidColumnsMatchException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use function array_flip;

/**
 * @author Manuel Aguirre
 */
class SelectColumnsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ColumnsMatchInfo $matchInfo */
        $matchInfo = $options['match_info'];
        $selectChoices = array_flip($matchInfo->getFileHeaders());

        foreach ($matchInfo->getConfigColumns()->getColumnsWithLabels() as $columnName => $columnLabel) {
            $required = $matchInfo->getConfigColumns()->isRequired($columnName);
            $builder->add($columnName, ChoiceType::class, [
                'placeholder' => '--------------',
                'choices' => $selectChoices,
                'data' => $matchInfo->getFileColumnMatch($columnName),
                'required' => $required,
                ...($required ? ['constraints' => new NotBlank()] : []),
            ]);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event)
        use ($matchInfo, $options) {
            $form = $event->getForm();

            try {
                $matchInfo->validateWith($event->getData());
            } catch (InvalidColumnsMatchException $exception) {
                if ($options['throw_exceptions']) {
                    throw $exception;
                }

                $form->addError(new FormError($exception->getMessage()));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('match_info');
        $resolver->setAllowedTypes('match_info', ColumnsMatchInfo::class);

        $resolver->setDefault('throw_exceptions', false);
        $resolver->setAllowedTypes('throw_exceptions', 'bool');
    }
}
