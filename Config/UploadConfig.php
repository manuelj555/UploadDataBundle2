<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Manuelj555\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuelj555\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadRepository;
use Manuelj555\Bundle\UploadDataBundle\Form\Type\UploadType;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ListMapper;
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
    private $columnsMapper;
    private $validationBuilder;
    private $listMapper;
    /**
     * @var ReaderLoader
     */
    private $readerLoader;
    private $columnListFactory;
    private $processed = false;
    private $uploadDir = false;
    private $type = false;

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

    public function __construct()
    {
        $this->columnsMapper = new ColumnsMapper();
        $this->validationBuilder = new ValidationBuilder();
    }

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Manuelj555\Bundle\UploadDataBundle\Mapper\ListMapper $listMapper
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

    public function getQueryList(Request $request, UploadRepository $repository)
    {
        return $repository->getQueryForType($this->getType());
    }

    abstract public function configureColumns(ColumnsMapper $mapper);

    public function configureList(ListMapper $mapper)
    {
        $mapper->add('id', null, array('use_show' => true,));
        $mapper->add('filename', 'link', array(
            'route' => 'upload_data_upload_show',
            'condition' => function (Upload $upload) { return $upload->getTotal() > 0; },
        ));
        $mapper->add('uploadedAt', 'datetime');
        $mapper->add('total', 'number', array('use_show' => true,));
        $mapper->add('valids', 'number', array('use_show' => true,));
        $mapper->add('invalids', 'number', array('use_show' => true,));
        $mapper->addAction('read', array(
            'condition' => function (Upload $upload) { return $upload->isReadable(); },
            'route' => 'upload_data_upload_read',
            'modal' => true,
        ));
        $mapper->addAction('validate', array(
            'condition' => function (Upload $upload) { return $upload->isValidatable(); },
            'route' => 'upload_data_upload_validate',
        ));
        $mapper->addAction('transfer', array(
            'condition' => function (Upload $upload) { return $upload->isTransferable(); },
            'route' => 'upload_data_upload_transfer',
        ));
        $mapper->addAction('delete', array(
            'condition' => function (Upload $upload) { return $upload->isDeletable(); },
            'route' => 'upload_data_upload_delete',
            'confirm_text' => 'upload_data.confirm_delete'
        ));
    }

    abstract public function configureValidations(ValidationBuilder $builder);

    public function getColumnsMapper()
    {
        return $this->columnsMapper;
    }

    /**
     * @return \Manuelj555\Bundle\UploadDataBundle\Mapper\ListMapper
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

    public function processUpload(UploadedFile $file, array $formData = array())
    {
        $upload = $this->getInstance();

        $upload->setFilename($file->getClientOriginalName());
        $upload->setType($this->getType());

        $this->objectManager->beginTransaction();

        $this->onPreUpload($upload, $file, $formData);

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $file = $file->move($this->uploadDir, $upload->getId() . '.' . $file->getClientOriginalExtension());

        $upload->setFile($file->getFilename());
        $upload->setFullFilename($file->getLinkTarget());

        $this->onPostUpload($upload, $file, $formData);

        $this->objectManager->flush();
        $this->objectManager->commit();

    }

    public function processRead(Upload $upload)
    {
        if (!$upload->isReadable() or !$upload->getAttribute('config_read')) {
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
                $uploadedItem->setData($item);
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
        if (!$upload->isValidatable()) {
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
        if (!$upload->isTransferable()) {
            return false;
        }

        $action = $upload->getAction('transfer');

        try {
            $action->setInProgress();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->transfer($upload, $upload->getItems());

            $action->setComplete();

            $this->objectManager->persist($upload);
            $this->objectManager->flush();

            $this->onPostRead($upload);

        } catch (\Exception $e) {
            $this->onActionException($action, $upload);

            throw $e;
        }
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function processAction(Upload $upload, $name) { }

    public function onPreUpload(Upload $upload, File $file, array $formData = array()) { }

    public function onPostUpload(Upload $upload, File $file, array $formData = array()) { }

    public function onPreRead(Upload $upload) { }

    public function onPostRead(Upload $upload) { }

    public function onPreValidate(Upload $upload) { }

    public function validateItem(UploadedItem $item, ContextualValidatorInterface $context, Upload $upload) { }

    public function onPostValidate(Upload $upload) { }

    abstract public function transfer(Upload $upload, Collection $items);

    public function onPreDelete(Upload $upload) { }

    public function onPostDelete(Upload $upload) { }

    private function onActionException($action, $upload)
    {
        if ($this->objectManager->isOpen()) {
            $action->setNotComplete();
            $this->objectManager->persist($upload);
            $this->objectManager->flush();
        }
    }
}