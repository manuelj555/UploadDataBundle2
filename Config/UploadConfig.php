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
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ListMapper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadConfig
{
    protected $columnsMapper;
    protected $validationBuilder;
    protected $listMapper;
    /**
     * @var ReaderLoader
     */
    protected $readerLoader;
    protected $columnListFactory;
    private $processed = false;
    protected $uploadDir = false;
    protected $type = false;

    /**
     * @var EntityManagerInterface
     */
    protected $objectManager;
    /**
     * @var ValidatorInterface
     */
    protected $validator;

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

    public function configureColumns(ColumnsMapper $mapper)
    {

    }

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

    public function configureValidations(ValidationBuilder $builder)
    {

    }

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

    public function processUpload(UploadedFile $file)
    {
        $upload = $this->getInstance();

        $upload->setFilename($file->getClientOriginalName());
        $upload->setType($this->getType());

        $this->objectManager->beginTransaction();

        $this->onPreUpload($upload, $file);

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $file = $file->move($this->uploadDir, $upload->getId() . '.' . $file->getClientOriginalExtension());

        $upload->setFile($file->getFilename());
        $upload->setFullFilename($file->getLinkTarget());

        $this->onPostUpload($upload, $file);

        $this->objectManager->flush();
        $this->objectManager->commit();

    }

    public function processRead(Upload $upload)
    {
        if (!$upload->isReadable() or !$upload->getAttribute('config_read')) {
            return false;
        }

        $action = $upload->getAction('read');
        $action->setInProgress();
        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPreRead($upload);

        $reader = $this->readerLoader->get($upload->getFullFilename());

        $data = $reader->getData($upload->getFullFilename(), $upload->getAttribute('config_read')->getValue());

        foreach ($data as $item) {

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
    }

    public function processValidation(Upload $upload)
    {
        if (!$upload->isValidatable()) {
            return false;
        }

        $action = $upload->getAction('validate');

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

            $violations = $context->getViolations();

            $this->validateItem($data, $violations, $upload);

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

        $this->onPostRead($upload);
    }

    public function processTransfer(Upload $upload)
    {
        if (!$upload->isTransferable()) {
            return false;
        }

        $action = $upload->getAction('transfer');
        $action->setInProgress();
        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->transfer($upload, $upload->getItems());

        $action->setComplete();

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPostRead($upload);
    }

    public function processAction(Upload $upload, $name)
    {

    }

    public function onPreUpload(Upload $upload, File $file) { }

    public function onPostUpload(Upload $upload, File $file) { }

    public function onPreRead() { }

    public function onPostRead() { }

    public function onPreValidate() { }

    public function validateItem(array $data, ConstraintViolationListInterface $violations , Upload $upload) { }

    public function onPostValidate() { }

    public function transfer($data, Collection $items) { }

    public function onPreDelete() { }

    public function onPostDelete() { }
}