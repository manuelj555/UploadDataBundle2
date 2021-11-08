<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Validator;

use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Context\ExecutionContextFactoryInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class UploadedItemValidator
{
    /**
     * @var ExecutionContextFactoryInterface
     */
    private $contextFactory;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;

        $this->contextFactory = new ExecutionContextFactory(
            $translator, 'validators'
        );
    }

    public function createValidationContext(UploadedItem $item): ContextualValidatorInterface
    {
        return $this->validator->inContext($this->contextFactory->createContext(
            $this->validator,
            $item
        ));
    }
}