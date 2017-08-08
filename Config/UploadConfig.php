<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Manuel\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Manuel\Bundle\UploadDataBundle\Form\Type\UploadType;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuel\Bundle\UploadDataBundle\Mapper\ListMapper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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

    public function processConfiguration()
    {
        if ($this->processed) {
            return;
        }

        $this->processed = true;

        $this->configureColumns($this->columnsMapper);
        $this->configureValidations($this->validationBuilder);
        $this->configureList($this->listMapper);
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

        return $repository->getQueryForType($this->getType(), $search, $order);
    }

    abstract public function configureColumns(ColumnsMapper $mapper);

    public function getColumnsForShow()
    {
        $columns = array_chunk($this->getColumnsMapper()->getNames(), 6);

        return current($columns);
    }

    /**
     * @param ListMapper $mapper
     */
    public function configureList(ListMapper $mapper)
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

    abstract public function configureValidations(ValidationBuilder $builder);

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
            throw new \InvalidArgumentException('No existe el template '.$name);
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

        return $upload;
    }

    public function createUploadForm()
    {
        return new UploadType();
    }

    public function processUpload(UploadedFile $file, array $formData = array(), array $attributes = [])
    {
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

        $file = $file->move($this->uploadDir, $upload->getId().'.'.$file->getClientOriginalExtension());

        $upload->setFile($file->getFilename());
        $upload->setFullFilename($file->getRealPath());

        $this->onPostUpload($upload, $file, $formData);

        $this->objectManager->flush();
        $this->objectManager->commit();

        return $upload;

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

            $data = $reader->getData($upload->getFullFilename(), $upload->getAttribute('config_read')->getValue());

            $columnsMapper = $this->columnsMapper->getColumns();

            foreach ($data as $item) {
                $formattedItem = array();

                foreach ($item as $colName => $value) {
                    if (isset($columnsMapper[$colName])) {
                        $formattedItem[$colName] = call_user_func(
                            $columnsMapper[$colName]['formatter'],
                            $value
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

            throw $e;
        }
    }

    public function processValidation(Upload $upload)
    {
        if (!$this->isValidatable($upload)) {
            return false;
        }

        $action = $upload->getAction('validate');

        try {
            $validationGroup = $action->isComplete() ? 'upload-revalidate' : 'upload-validate';

            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPreValidate($upload);

            $validations = $this->validationBuilder->getValidations();

            $valids = $invalids = 0;

            foreach ($upload->getItems() as $item) {
                $context = $this->validator->startContext();
                $data = $item->getData();
                foreach ($validations as $column => $constraints) {
                    $value = array_key_exists($column, $data) ? $data[$column] : null;
                    $context->atPath($column)->validate($value, $constraints, array('Default', $validationGroup));
                }

                $this->validateItem($item, $context, $upload);

                $violations = $context->getViolations();

                if (count($violations)) {
                    $item->setErrors($context->getViolations());
                    ++$invalids;
                } else {
                    $item->setErrors(null);
                    ++$valids;
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

            $action->setComplete();

            $this->objectManager->persist($upload);
            $this->objectManager->flush();
        } catch (\Exception $e) {
            $this->onActionException($action, $upload);

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
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function processActionByName(Upload $upload, $name)
    {
        $action = $upload->getAction($name);

        if (null == $action) {
            return false;
        }

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->processAction($upload, $action);

            $action->setComplete();

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

        } catch (\Exception $e) {
            $this->onActionException($action, $upload);

            throw $e;
        }
    }

    protected function processAction(Upload $upload, UploadAction $action)
    {
    }

    public function onPreUpload(Upload $upload, File $file, array $formData = array())
    {
    }

    public function onPostUpload(Upload $upload, File $file, array $formData = array())
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
        return $upload->isDeletable();
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isTransferable(Upload $upload)
    {
        return $upload->isTransferable();
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isValidatable(Upload $upload)
    {
        return $upload->isValidatable();
    }

    /**
     * @param Upload $upload
     * @return bool
     */
    public function isReadable(Upload $upload)
    {
        return $upload->isReadable();
    }

    private function onActionException($action, $upload)
    {
        if ($this->objectManager->isOpen()) {
            $action->setNotComplete();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();
        }
    }
}
