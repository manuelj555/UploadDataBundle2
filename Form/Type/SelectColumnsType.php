<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Form\Type;

use Manuel\Bundle\UploadDataBundle\Data\ColumnsMatchInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use function array_filter;
use function array_flip;
use function array_unique;
use function count;

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
        $requiredColumnsCount = 0;

        foreach ($matchInfo->getConfigColumns()->getColumnsWithLabels() as $columnName => $columnLabel) {
            $required = $matchInfo->getConfigColumns()->isRequired($columnName);
            $builder->add($columnName, ChoiceType::class, [
                'placeholder' => '--------------',
                'choices' => $selectChoices,
                'data' => $matchInfo->getFileColumnMatch($columnName),
                'required' => $required,
                ...($required ? ['constraints' => new NotBlank()] : []),
            ]);

            $required && $requiredColumnsCount++;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event)
        use ($requiredColumnsCount) {
            $data = $event->getData();
            $form = $event->getForm();
            $matchedColumns = array_filter($data);

            if (count($matchedColumns) < $requiredColumnsCount) {
                $form->addError(new FormError('upload.required_fields'));
            }

            if ($matchedColumns != array_unique($matchedColumns)) {
                $form->addError(new FormError('upload.repeated_items'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('match_info');
        $resolver->setAllowedTypes('match_info', ColumnsMatchInfo::class);
    }
}
