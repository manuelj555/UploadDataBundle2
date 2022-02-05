<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Data\UploadedFileHelperInterface;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuel\Bundle\UploadDataBundle\Exception\UploadProcessException;
use Manuel\Bundle\UploadDataBundle\Profiler\ExceptionProfiler;
use Manuel\Bundle\UploadDataBundle\Validator\ColumnError;
use Manuel\Bundle\UploadDataBundle\Validator\GroupedConstraintViolations;
use Manuel\Bundle\UploadDataBundle\Validator\UploadedItemValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Throwable;
use function dd;
use function dump;
use function md5;
use function sprintf;
use function uniqid;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadConfigHandler
{
    public function __construct(
        protected EntityManagerInterface $objectManager,
        private ReaderLoader $readerLoader,
        private UploadedItemValidator $validator,
        private UploadedFileHelperInterface $uploadedFileHelper,
        private ExceptionProfiler $exceptionProfiler,
        private string $uploadDir,
    ) {
    }

    public function processUpload(
        ResolvedUploadConfig $resolvedConfig,
        UploadedFile $file,
        array $formData = [],
        array $attributes = [],
    ): Upload {
        $config = $resolvedConfig->getConfig();

        try {
            $upload = $config->getInstance();
            $upload->setFilename($file->getClientOriginalName());
            $upload->setType($config->getUploadType());

            foreach ($attributes as $name => $attrValue) {
                $upload->setAttributeValue($name, $attrValue);
            }

            $this->objectManager->beginTransaction();

            if ($config instanceof ConfigUploadFiltersAwareInterface) {
                $config->onPreUpload($upload, $file, $formData);
            }

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $newFilename = $this->createUniqueFilename($file, $upload);
            $filename = $this->uploadedFileHelper->saveFile($file, $this->uploadDir, $newFilename);

            $upload->setFile(basename($filename));
            $upload->setFullFilename($filename);

            if ($config instanceof ConfigUploadFiltersAwareInterface) {
                $config->onPostUpload($upload, $filename, $formData);
            }

            $this->objectManager->persist($upload);
            $this->objectManager->flush();
            $this->objectManager->commit();

            return $upload;
        } catch (\Exception $e) {
            $this->profileException($e);

            throw new UploadProcessException($e, 'upload');
        }
    }

    public function processRead(ResolvedUploadConfig $resolvedConfig, Upload $upload): bool
    {
        $config = $resolvedConfig->getConfig();

        if (!$config->isActionable($upload, 'read')) {
            return false;
        }

        $action = $upload->getAction('read');

        try {
            $this->setInProcessAction($action);

            if ($config instanceof ConfigReadFiltersAwareInterface) {
                $config->onPreRead($upload);
            }

            $reader = $this->readerLoader->get($upload);
            $data = $reader->getData($upload);

            $columnsMapper = $resolvedConfig->getConfigColumns()->getColumns();

            foreach ($data as $dataRowNumber => $item) {
                $formattedItemData = [];

                foreach ($item as $colName => $value) {
                    if (isset($columnsMapper[$colName])) {
                        if (is_array($value)) {
                            $withFormat = $value['with_format'];
                            $withoutFormat = $value['without_format'];
                        } else {
                            $withFormat = $value;
                            $withoutFormat = $value;
                        }

                        $formattedItemData[$colName] = call_user_func(
                            $columnsMapper[$colName]['formatter'],
                            $withFormat, $withoutFormat
                        );
                    }
                }

                $uploadedItem = $upload->addItem($formattedItemData, $dataRowNumber);
                $this->objectManager->persist($uploadedItem);
            }

            $upload->setTotal(count($data));
            $this->completeAction($upload, $action);

            if ($config instanceof ConfigReadFiltersAwareInterface) {
                $config->onPostRead($upload);
            }
        } catch (\Exception $e) {
            $this->onActionException($e, $action, $upload);
        }

        return true;
    }

    public function processValidation(
        ResolvedUploadConfig $resolvedConfig,
        Upload $upload,
        $onlyInvalids = false,
    ): bool {
        $config = $resolvedConfig->getConfig();

        if (!$config->isActionable($upload, 'validate')) {
            return false;
        }

        $action = $upload->getAction('validate');
        $isActionCompleted = $action->isComplete();

        try {
            $validationGroup = $isActionCompleted ? 'upload-revalidate' : 'upload-validate';
            $this->setInProcessAction($action);

            if ($config instanceof ConfigValidateFiltersAwareInterface) {
                $config->onPreValidate($upload);
            }

            $validations = $resolvedConfig->getConfigColumns()->getValidations();
            $valid = $invalids = 0;
            $items = $upload->getItems();

            if ($isActionCompleted && $onlyInvalids) {
                $valid = $upload->getValids();

                $items = $items->filter(function (UploadedItem $item) use ($config) {
                    return !$config->isAlreadyProcessedItemValid($item);
                });
            }

            /** @var UploadedItem $item */
            foreach ($items as $item) {
                if ($config->itsAnExcludedItem($item)) {
                    $item->setValid(true);
                    ++$valid;

                    $this->objectManager->persist($item);
                    continue;
                }

                $violations = new GroupedConstraintViolations();
                $data = $item->getData();
                foreach ($validations as $group => $columnValidations) {
                    $context = $this->validator->createValidationContext($item);
                    foreach ($columnValidations as $column => $constraints) {
                        $value = array_key_exists($column, $data) ? $data[$column] : null;
                        $context->atPath($column)->validate($value, $constraints, array('Default', $validationGroup));
                    }

                    // por cada categoria|grupo de validaciones, toca saber si hubieron errores.
                    $violations->addAll($group, $context->getViolations());
                }

                if ($violations->hasViolationsForGroup('default')) {
                    // Si hay errores en el grupo por defecto, lo marcamos en el item.
                    $item->setHasDefaultErrors();
                    // esto con la finalidad de poder obviar validaciones propias, cuando las
                    // validaciones mínimas no fueron superadas.
                }

                // iniciamos un nuevo contexto para las validaciones propias.
                $context = $this->validator->createValidationContext($item);
                $config->validateItem($item, $context, $upload);

                $this->mergeViolations($violations, $context);

                $item->setErrors($violations);
                $item->setValid($config->shouldItemCanBeConsideredAsValid($violations, $item));
                if ($item->getValid()) {
                    ++$valid;
                } else {
                    ++$invalids;
                }

                $this->objectManager->persist($item);
            }

            $upload->setValids($valid);
            $upload->setInvalids($invalids);
            $this->completeAction($upload, $action);

            if ($config instanceof ConfigValidateFiltersAwareInterface) {
                $config->onPostValidate($upload);
            }
        } catch (\Exception $e) {
            $this->onActionException($e, $action, $upload);
        }

        return true;
    }

    public function processTransfer(ResolvedUploadConfig $resolvedConfig, Upload $upload): bool
    {
        $config = $resolvedConfig->getConfig();

        if (!$config->isActionable($upload, 'transfer')) {
            return false;
        }

        $action = $upload->getAction('transfer');

        try {
            $this->setInProcessAction($action);

            $config->transfer($upload);

            $this->completeAction($upload, $action);
        } catch (\Exception $e) {
            $this->onActionException($e, $action, $upload);
        }

        return true;
    }

    public function processDelete(ResolvedUploadConfig $resolvedConfig, Upload $upload): bool
    {
        $config = $resolvedConfig->getConfig();

        if (!$config->isActionable($upload, 'delete')) {
            return false;
        }

        $action = $upload->getAction('delete');

        try {
            $this->setInProcessAction($action);

            if ($config instanceof ConfigDeleteFiltersAwareInterface) {
                $config->onPreDelete($upload);
            }

            $this->objectManager->remove($upload);
            $action->setComplete();
            $this->objectManager->flush();

            if ($config instanceof ConfigDeleteFiltersAwareInterface) {
                $config->onPostDelete($upload);
            }
        } catch (\Exception $e) {
            $this->onActionException($e, $action, $upload);
        }

        return true;
    }

    public function processActionByName(ResolvedUploadConfig $resolvedConfig, Upload $upload, string $name): bool
    {
        $config = $resolvedConfig->getConfig();

        if (!$config->isActionable($upload, $name)) {
            return false;
        }

        $action = $upload->getAction($name);

        try {
            $this->setInProcessAction($action);

            $config->processAction($upload, $action);

            $this->completeAction($upload, $action);
        } catch (\Exception $e) {
            $this->onActionException($e, $action, $upload);
        }

        return true;
    }

    private function setInProcessAction(UploadAction $action): void
    {
        $action->setInProgress();
        $this->objectManager->persist($action);
        $this->objectManager->flush();
    }

    private function completeAction(Upload $upload, UploadAction $action): void
    {
        $action->setComplete();
        $this->objectManager->persist($upload);
        $this->objectManager->persist($action);
        $this->objectManager->flush();
    }

    private function createUniqueFilename(UploadedFile $file, Upload $upload): string
    {
        return sprintf(
            '%d_%s_%s.%s',
            $upload->getId(),
            $upload->getUploadedAt()->format('Ymd_his'),
            md5(uniqid($upload->getId() . $file->getClientOriginalName())),
            $file->getClientOriginalExtension()
        );
    }

    protected function profileException(Throwable $e)
    {
        $this->exceptionProfiler->addException($e);
    }

    private function onActionException(Throwable $exception, UploadAction $action, Upload $upload): void
    {
        try {
            if ($this->objectManager->isOpen()) {
                $action->setNotComplete();
                $this->objectManager->persist($upload);
                $this->objectManager->flush($upload);
            }
        } catch (\Exception $e) {
        }

        $this->profileException($exception);

        throw new UploadProcessException($exception, $action->getName());
    }

    private function mergeViolations(
        GroupedConstraintViolations $violations,
        ContextualValidatorInterface $context,
    ): void {
        foreach ($context->getViolations() as $violation) {
            if ($violation instanceof ColumnError) {
                $violations->addColumnError($violation);
            } else {
                $violations->add('default', $violation);
            }
        }
    }
}
