<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Manuel\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Data\UploadedFileHelperInterface;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Manuel\Bundle\UploadDataBundle\Form\Type\UploadType;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuel\Bundle\UploadDataBundle\Mapper\ListMapper;
use Manuel\Bundle\UploadDataBundle\Profiler\ExceptionProfiler;
use Manuel\Bundle\UploadDataBundle\Validator\ColumnError;
use Manuel\Bundle\UploadDataBundle\Validator\GroupedConstraintViolations;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class UploadConfig
{
    /**
     * @var ColumnsMapper
     */
    private $columnsMapper;
    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;
    /**
     * @var ListMapper
     */
    private $listMapper;
    /**
     * @var ReaderLoader
     */
    private $readerLoader;
    private $columnListFactory;
    private $processed = false;
    private $uploadDir = false;
    private $type = false;
    private $showDeleted = true;
    private $label;

    /**
     * @var EntityManagerInterface
     */
    protected $objectManager;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UploadedFileHelperInterface
     */
    private $uploadedFileHelper;

    /**
     * @var ExceptionProfiler
     */
    private $exceptionProfiler;
    /**
     * @var array
     */
    private $templates = array();

    public function initialize()
    {
        $this->columnsMapper = new ColumnsMapper();
        $this->validationBuilder = new ValidationBuilder();
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param bool $showDeleted
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;
    }

    /**
     * @return bool
     */
    public function isShowDeleted()
    {
        return $this->showDeleted;
    }

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Manuel\Bundle\UploadDataBundle\Mapper\ListMapper $listMapper
     */
    public function setListMapper($listMapper)
    {
        $this->listMapper = $listMapper;
    }

    /**
     * @param mixed $columnListFactory
     */
    public function setColumnListFactory($columnListFactory)
    {
        $this->columnListFactory = $columnListFactory;
    }

    /**
     * @param mixed $readerLoader
     */
    public function setReaderLoader($readerLoader)
    {
        $this->readerLoader = $readerLoader;
    }

    /**
     * @param boolean $uploadDir
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param UploadedFileHelperInterface $uploadedFileHelper
     */
    public function setUploadedFileHelper($uploadedFileHelper)
    {
        $this->uploadedFileHelper = $uploadedFileHelper;
    }

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ExceptionProfiler $exceptionProfiler
     */
    public function setExceptionProfiler(ExceptionProfiler $exceptionProfiler)
    {
        $this->exceptionProfiler = $exceptionProfiler;
    }

    /**
     * @param boolean $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return boolean
     */
    public function getType()
    {
        return $this->type;
    }

    public function processConfiguration($options = [])
    {
        if ($this->processed) {
            return;
        }

        $this->processed = true;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        $this->configureColumns($this->columnsMapper, $options);
        $this->configureValidations($this->validationBuilder, $options);
        $this->configureList($this->listMapper, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getViewPrefix()
    {
        return '@UploadData/Upload';
    }

    public function getQueryList(UploadRepository $repository, $filters = null, $order = 'DESC')
    {
        if (is_array($filters) and array_key_exists('search', $filters)) {
            $search = $filters['search'];
        } else {
            $search = null;
        }

        $queryBuilder = $repository->getQueryForType($this->getType(), $search, $order);

        if (!$this->isShowDeleted()) {
            $this->addDeleteExclusionFilter($queryBuilder);
        }

        return $queryBuilder;
    }

    abstract public function configureColumns(ColumnsMapper $mapper, array $options);

    public function getColumnsForShow()
    {
        $columns = array_chunk($this->getColumnsMapper()->getNames(), 6);

        return current($columns);
    }

    /**
     * @param ListMapper $mapper
     * @param array $options
     */
    public function configureList(ListMapper $mapper, array $options)
    {
        $uploadConfig = $this;

        $mapper->add('id', null, array('use_show' => true,));
        $mapper->add('filename', 'link', array(
            'route' => 'upload_data_upload_show',
            'condition' => function (Upload $upload) {
                return $upload->getTotal() > 0;
            },
        ));
        $mapper->add('uploadedAt', 'datetime');
        $mapper->add('total', 'number_link', array(
            'position' => 10,
            'use_show' => true,
            'route' => 'upload_data_upload_show',
            'condition' => function (Upload $upload) {
                return $upload->getTotal() > 0;
            },
        ));
        $mapper->add('valids', 'number_link', array(
            'position' => 20,
            'use_show' => true,
            'route' => 'upload_data_upload_show',
            'condition' => function (Upload $upload) {
                return $upload->getValids() > 0;
            },
            'parameters' => array('valid' => 1),
        ));
        $mapper->add('invalids', 'number_link', array(
            'position' => 30,
            'use_show' => true,
            'route' => 'upload_data_upload_show',
            'condition' => function (Upload $upload) {
                return $upload->getInvalids() > 0;
            },
            'parameters' => array('valid' => 0),
        ));
        $mapper->addAction('read', array(
            'position' => 100,
            'condition' => function (Upload $upload) use ($uploadConfig) {
                return $uploadConfig->isReadable($upload);
            },
            'route' => 'upload_data_upload_read',
            'modal' => true,
        ));
        $mapper->addAction('validate', array(
            'position' => 200,
            'condition' => function (Upload $upload) use ($uploadConfig) {
                return $uploadConfig->isValidatable($upload);
            },
            'route' => 'upload_data_upload_validate',
        ));
        $mapper->addAction('transfer', array(
            'position' => 300,
            'condition' => function (Upload $upload) use ($uploadConfig) {
                return $uploadConfig->isTransferable($upload);
            },
            'route' => 'upload_data_upload_transfer',
        ));
        $mapper->addAction('delete', array(
            'position' => 500,
            'condition' => function (Upload $upload) use ($uploadConfig) {
                return $uploadConfig->isDeletable($upload);
            },
            'route' => 'upload_data_upload_delete',
            'confirm_text' => 'upload_data.confirm_delete',
        ));
    }

    abstract public function configureValidations(ValidationBuilder $builder, array $options);

    public function getColumnsMapper()
    {
        return $this->columnsMapper;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    public function getTemplate($name)
    {
        if (!isset($this->templates[$name])) {
            throw new \InvalidArgumentException('No existe el template ' . $name);
        }

        return $this->templates[$name];
    }

    public function setTemplate($name, $value)
    {
        $this->templates[$name] = $value;
    }

    /**
     * @return \Manuel\Bundle\UploadDataBundle\Mapper\ListMapper
     */
    public function getListMapper()
    {
        return $this->listMapper;
    }

    public function getInstance()
    {
        $upload = new Upload();
        $upload->setAttributeValue('configured_columns', $this->getColumnsMapper()->getColumnsAsArray());

        return $upload;
    }

    public function createUploadForm()
    {
        return UploadType::class;
    }

    public function processUpload(UploadedFile $file, array $formData = array(), array $attributes = [])
    {
        try {
            $upload = $this->getInstance();

            $upload->setFilename($file->getClientOriginalName());
            $upload->setType($this->getType());

            foreach ($attributes as $name => $attrValue) {
                $upload->setAttributeValue($name, $attrValue);
            }

            $this->objectManager->beginTransaction();

            $this->onPreUpload($upload, $file, $formData);

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $newFilename = $this->createUniqueFilename($file, $upload);
            $filename = $this->uploadedFileHelper->saveFile($file, $this->uploadDir, $newFilename);

            $upload->setFile(basename($filename));
            $upload->setFullFilename($filename);

            $this->onPostUpload($upload, $filename, $formData);

            $this->objectManager->flush();
            $this->objectManager->commit();

            return $upload;
        } catch (\Exception $e) {
            $this->profileException($e);

            throw $e;
        }

    }

    public function processRead(Upload $upload)
    {
        if (!$this->isReadable($upload) or !$upload->getAttribute('config_read')) {
            return false;
        }

        $action = $upload->getAction('read');

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPreRead($upload);

            $reader = $this->readerLoader->get($upload->getFullFilename());

            $data = $reader->getData($upload->getFullFilename(), $upload->getAttributeValue('config_read'));

            $columnsMapper = $this->columnsMapper->getColumns();

            foreach ($data as $item) {
                $formattedItem = array();

                foreach ($item as $colName => $value) {
                    if (isset($columnsMapper[$colName])) {
                        if (is_array($value)) {
                            $withFormat = $value['with_format'];
                            $withoutFormat = $value['without_format'];
                        } else {
                            $withFormat = $value;
                            $withoutFormat = $value;
                        }

                        $formattedItem[$colName] = call_user_func(
                            $columnsMapper[$colName]['formatter'],
                            $withFormat, $withoutFormat
                        );
                    }
                }
                $uploadedItem = new UploadedItem();
                $uploadedItem->setData($formattedItem);
                $upload->addItem($uploadedItem);

                $this->objectManager->persist($uploadedItem);
            }

            $action->setComplete();
            $upload->setTotal(count($data));

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPostRead($upload);
        } catch (\Exception $e) {
            $this->onActionException($action, $upload);
            $this->profileException($e);

            throw $e;
        }
    }

    public function processValidation(Upload $upload, $onlyInvalids = false)
    {
        if (!$this->isValidatable($upload)) {
            return false;
        }

        $action = $upload->getAction('validate');
        $isActionCompleted = $action->isComplete();

        try {
            $validationGroup = $isActionCompleted ? 'upload-revalidate' : 'upload-validate';

            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPreValidate($upload);

            $validations = $this->validationBuilder->getValidations();
            $valids = $invalids = 0;
            $items = $upload->getItems();

            if ($isActionCompleted && $onlyInvalids) {
                $valids = $upload->getValids();

                $items = $items->filter(function (UploadedItem $item) {
                    return !$this->isUploadedItemValid($item);
                });
            }

            /** @var UploadedItem $item */
            foreach ($items as $item) {
                $violations = new GroupedConstraintViolations();
                $data = $item->getData();
                foreach ($validations as $group => $columnValidations) {
                    $context = $this->validator->startContext($item);
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
                $context = $this->validator->startContext($item);
                $this->validateItem($item, $context, $upload);

                $this->mergeViolations($violations, $context);

                $item->setErrors($violations);
                $item->setIsValid($this->shouldItemCanBeConsideredAsValid($violations, $item));
                if ($item->getIsValid()) {
                    ++$valids;
                } else {
                    ++$invalids;
                }

                $this->objectManager->persist($item);
            }

            $action->setComplete();
            $upload->setValids($valids);
            $upload->setInvalids($invalids);

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPostValidate($upload);
        } catch (\Exception $e) {
            $this->profileException($e);
            $this->onActionException($action, $upload);

            throw $e;
        }
    }

    public function processTransfer(Upload $upload)
    {
        if (!$this->isTransferable($upload)) {
            return false;
        }

        $action = $upload->getAction('transfer');

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $data = $this->transfer($upload, $upload->getItems());

            if (!$this->objectManager->contains($upload)) {
                $upload = $this->objectManager->merge($upload);
            }

            if (!$this->objectManager->contains($action)) {
                $action = $this->objectManager->merge($action);
            }

            $action->setComplete();

            $this->objectManager->persist($upload);
            $this->objectManager->flush();
        } catch (\Exception $e) {
            $this->onActionException($action, $upload);
            $this->profileException($e);

            throw $e;
        }

        return $data;
    }

    public function processDelete(Upload $upload)
    {
        if (!$this->isDeletable($upload)) {
            return false;
        }

        $action = $upload->getAction('delete');

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPreDelete($upload);

            $this->objectManager->remove($upload);
            $this->objectManager->flush();

            $action->setComplete();

            $this->onPostDelete($upload);

        } catch (\Exception $e) {
            $this->onActionException($action, $upload);
            $this->profileException($e);

            throw $e;
        }
    }

    /**
     * Translates the given message.
     *
     * @param string $id The message id (may also be an object that can be cast to string)
     * @param array $parameters An array of parameters for the message
     * @param string|null $domain The domain for the message or null to use the default
     * @param string|null $locale The locale or null to use the default
     *
     * @return string The translated string
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function processActionByName(Upload $upload, $name)
    {
        if (!$this->isActionable($upload, $name)) {
            return false;
        }

        $action = $upload->getAction($name);

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->callActionFilter($upload, $name, 'onPre');

            $this->processAction($upload, $action);

            $action->setComplete();

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->callActionFilter($upload, $name, 'onPost');

        } catch (\Exception $e) {
            $this->onActionException($action, $upload);
            $this->profileException($e);

            throw $e;
        }
    }

    protected function processAction(Upload $upload, UploadAction $action)
    {
    }

    public function onPreUpload(Upload $upload, File $file, array $formData = array())
    {
    }

    public function onPostUpload(Upload $upload, $filename, array $formData = array())
    {
    }

    public function onPreRead(Upload $upload)
    {
    }

    public function onPostRead(Upload $upload)
    {
    }

    public function onPreValidate(Upload $upload)
    {
    }

    public function validateItem(UploadedItem $item, ContextualValidatorInterface $context, Upload $upload)
    {
    }

    public function onPostValidate(Upload $upload)
    {
    }

    abstract public function transfer(Upload $upload, Collection $items);

    public function onPreDelete(Upload $upload)
    {
    }

    public function onPostDelete(Upload $upload)
    {
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isDeletable(Upload $upload)
    {
        return $this->isActionable($upload, 'delete');
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isTransferable(Upload $upload)
    {
        return $this->isActionable($upload, 'transfer');
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isValidatable(Upload $upload)
    {
        return $this->isActionable($upload, 'validate');
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isReadable(Upload $upload)
    {
        return $upload->isReadable();
    }

    /**
     * @param Upload $upload
     * @param $actionName
     * @return bool
     */
    public function isActionable(Upload $upload, $actionName)
    {
        if ($actionName == 'transfer') {
            return $upload->isTransferable();
        }

        if ($actionName == 'read') {
            return $upload->isReadable();
        }

        if ($actionName == 'validate') {
            return $upload->isValidatable();
        }

        if ($actionName == 'delete') {
            return $upload->isDeletable();
        }

        if ($action = $upload->getAction($actionName)) {
            return $action->isNotComplete();
        }

        return false;

    }
    
    
    /**
     * Este método es para cuando se quiere hacer un match automático en los procesos de carga,
     * es decir, que no se le quiere permitir al usuario hacer un match manual de las columnas
     * del excel.
     *
     * @param Upload $upload
     * @param array $options
     */
    public function configureDefaultMatch(Upload $upload, $options = [])
    {
        isset($options['row_headers']) || $options['row_headers'] = 1;

        $reader = $this->readerLoader->get($upload->getFullFilename());
        $headers = $reader->getRowHeaders($upload->getFullFilename(), $options);
        $mapping = $this->getColumnsMapper()->getDefaultMapping($headers);
        $options['header_mapping'] = $mapping;

        $upload->setAttributeValue('config_read', $options);
    }
    
    /**
     * @return EntityManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    private function onActionException($action, $upload)
    {
        try {
            if ($this->objectManager->isOpen()) {
                $action->setNotComplete();
                $this->objectManager->persist($upload);
                $this->objectManager->flush($upload);
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * @param UploadedFile $file
     * @param $upload
     * @return string
     */
    private function createUniqueFilename(UploadedFile $file, Upload $upload)
    {
        return sprintf(
            '%d_%s_%s.%s',
            $upload->getId(),
            $upload->getUploadedAt()->format('Ymd_his'),
            md5(uniqid($upload->getId() . $file->getClientOriginalName())),
            $file->getClientOriginalExtension()
        );
    }

    protected function addDeleteExclusionFilter(QueryBuilder $queryBuilder)
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->not(
                $queryBuilder->expr()->exists('
                SELECT act
                FROM UploadDataBundle:UploadAction act
                WHERE
                    act.upload = upload
                  AND
                    act.name = \'delete\'
                  AND
                    act.status = :action_status_completed
                ')
            )
        )
            ->setParameter('action_status_completed', Upload::STATUS_COMPLETE);
    }

    protected function addAttributeFilter(QueryBuilder $queryBuilder, $attribute, $value)
    {
        $alias = '_attr_' . time();

        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists("
                SELECT {$alias}
                FROM UploadDataBundle:UploadAttribute {$alias}
                WHERE
                    {$alias}.upload = upload
                  AND
                    {$alias}.name = :{$alias}_name
                  AND
                    {$alias}.value = :{$alias}_value
            ")
        )
            ->setParameter("{$alias}_name", $attribute)
            ->setParameter("{$alias}_value", $value);
    }

    /**
     * @param GroupedConstraintViolations $allViolations
     * @param ContextualValidatorInterface $context
     */
    private function mergeViolations(GroupedConstraintViolations $violations, ContextualValidatorInterface $context)
    {
        foreach ($context->getViolations() as $violation) {
            if ($violation instanceof ColumnError) {
                $violations->addColumnError($violation);
            } else {
                $violations->add('default', $violation);
            }
        }
    }

    /**
     * Determina cuando un item es considerado invalido y cuando es valido.
     *
     * Por defecto es invalido cuando hay errores de valicacion para la categoria|grupo por defecto.
     *
     * @param GroupedConstraintViolations $violations
     * @param UploadedItem $item
     * @return bool
     */
    protected function shouldItemCanBeConsideredAsValid(
        GroupedConstraintViolations $violations,
        UploadedItem $item
    )
    {
        return !$violations->hasViolationsForGroup('default');
    }

    public function profileException(\Exception $e)
    {
        $this->exceptionProfiler->addException($e);
    }

    /**
     * Determina si un item es completamente válido.
     *
     * @param UploadedItem $item
     * @return bool
     */
    protected function isUploadedItemValid(UploadedItem $item)
    {
        return $item->getIsValid();
    }

    /**
     * @param Upload $upload
     * @param $name
     * @param $prefix
     */
    private function callActionFilter(Upload $upload, $name, $prefix)
    {
        $methodPreActionName = $prefix . $this->camelize($name);
        if (method_exists($this, $methodPreActionName)) {
            $this->{$methodPreActionName}($upload);
        }
    }

    private function camelize($string)
    {
        $words = explode('_', str_replace('-', '_', $string));
        $words = array_map('ucfirst', $words);

        return implode('', $words);
    }
}
