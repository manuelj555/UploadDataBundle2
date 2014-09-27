<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadConfig
{
    protected $columnsMapper;
    private $processed = false;
    protected $uploadDir = false;
    protected $type = false;

    /**
     * @var EntityManagerInterface
     */
    protected $objectManager;

    public function __construct()
    {
        $this->columnsMapper = new ColumnsMapper();
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
    }

    public function configureColumns(ColumnsMapper $mapper)
    {

    }

    public function configureValidations($mapper)
    {
        $mapper
            ->add('first_name', array(
                new NotBlank(),
            ))
            ->add('last_name', 'text', array(
                'label' => 'Nombre de la Columna',
            ));
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
        $this->onPreRead($upload);

        $upload->setReaded(Upload::STATUS_IN_PROGRESS);
        $this->objectManager->persist($upload);
        $this->objectManager->flush();

        $upload->setReaded(Upload::STATUS_COMPLETE);
        $upload->setReadedAt(new \DateTime());

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

//    public function onPreTransfer() { }
//    public function onPostTransfer() { }
    public function transfer($data) { }

    public function onPreDelete() { }

    public function onPostDelete() { }
}