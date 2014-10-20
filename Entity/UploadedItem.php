<?php

namespace Manuelj555\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * UploadedItem
 *
 * @ORM\Table(name="upload_data_uploaded_item")
 * @ORM\Entity(repositoryClass="Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class UploadedItem implements \ArrayAccess
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Manuelj555\Bundle\UploadDataBundle\Entity\Upload", inversedBy="items")
     */
    private $upload;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array", nullable=true)
     */
    private $data;

    /**
     * @var array
     *
     * @ORM\Column(name="errors", type="array", nullable=true)
     */
    private $errors;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isValid", type="boolean", nullable=true)
     */
    private $isValid;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set upload
     *
     * @param string $upload
     *
     * @return UploadedItem
     */
    public function setUpload($upload)
    {
        $this->upload = $upload;

        return $this;
    }

    /**
     * Get upload
     *
     * @return string
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return UploadedItem
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set errors
     *
     * @param array $errors
     *
     * @return UploadedItem
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Get errors
     *
     * @return array|ConstraintViolationListInterface
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set isValid
     *
     * @param boolean $isValid
     *
     * @return UploadedItem
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function adjustValues()
    {
        if ($this->errors instanceof ConstraintViolationListInterface) {
            $errors = array();

            foreach ($this->errors as $error) {
                isset($errors[$error->getPropertyPath()]) || $errors[$error->getPropertyPath()] = array();
                $errors[$error->getPropertyPath()][] = $error->getMessage();
            }

            $this->setErrors($errors);
        }

        $this->setIsValid(count($this->errors) == 0);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }
}
