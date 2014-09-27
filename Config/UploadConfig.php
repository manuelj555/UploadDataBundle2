<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Manuelj555\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadConfig
{
    protected $columnsMapper;
    protected $validationBuilder;
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
    }

    public function configureColumns(ColumnsMapper $mapper)
    {

    }

    public function configureValidations(ValidationBuilder $builder)
    {

    }

    public function getColumnsMapper()
    {
        return $this->columnsMapper;
    }

    public function getInstance()
    {
        return new Upload();
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
        $upload->setReaded(Upload::STATUS_IN_PROGRESS);
        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPreRead($upload);

        $data = array(
            array(
                'name' => 'Name 1',
                'email' => 'programador.manuel@gmail.com',
                'years' => '25',
            ),
            array(
                'name' => 'Name 2',
                'email' => 'no email',
                'years' => '',
            ),
            array(
                'name' => 'Name 3',
            ),
        );

        foreach ($data as $item) {

            $uploadedItem = new UploadedItem();
            $uploadedItem->setData($item);
            $upload->addItem($uploadedItem);

            $this->objectManager->persist($uploadedItem);
        }

        $upload->setReaded(Upload::STATUS_COMPLETE);
        $upload->setReadedAt(new \DateTime());
        $upload->setTotal(count($data));

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPostRead($upload);
    }

    public function processValidation(Upload $upload)
    {
        $upload->setValidated(Upload::STATUS_IN_PROGRESS);
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
                $context->atPath($column)->validate($value, $constraints);
            }

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

        $upload->setValidated(Upload::STATUS_COMPLETE);
        $upload->setValidatedAt(new \DateTime());
        $upload->setValids($valids);
        $upload->setInvalids($invalids);

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPostRead($upload);
    }

    public function processTransfer(Upload $upload)
    {
        $upload->setTransfered(Upload::STATUS_IN_PROGRESS);
        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->transfer($upload, $upload->getItems());

        $upload->setTransfered(Upload::STATUS_COMPLETE);
        $upload->setTransferedAt(new \DateTime());

        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $this->onPostRead($upload);
    }

    public function onPreUpload(Upload $upload, File $file) { }

    public function onPostUpload(Upload $upload, File $file) { }

    public function onPreRead() { }

    public function onPostRead() { }

    public function onPreValidate() { }

    public function onPostValidate() { }

    public function transfer($data, Collection $items) { }

    public function onPreDelete() { }

    public function onPostDelete() { }
}