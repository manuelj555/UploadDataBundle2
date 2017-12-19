<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * UploadedItem
 *
 * @ORM\Table(name="upload_data_uploaded_item")
 * @ORM\Entity(repositoryClass="Manuel\Bundle\UploadDataBundle\Entity\UploadedItemRepository")
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
     * @ORM\ManyToOne(targetEntity="Manuel\Bundle\UploadDataBundle\Entity\Upload", inversedBy="items")
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
     * @ORM\Column(name="extras", type="json_array", nullable=true)
     */
    private $extras = array();

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
     * @var boolean
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

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

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param array $extras
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    public function setExtra($key, $value)
    {
        $this->extras[$key] = $value;
    }

    public function getExtra($key)
    {
        return $this->hasExtra($key) ? $this->extras[$key] : null;
    }

    public function hasExtra($key)
    {
        return array_key_exists($key, $this->extras);
    }

    public function getErrorsAsString($separator = ', ', $showKeys = false)
    {
        $errors = [];

        foreach ($this->getErrors() as $key => $data) {
            if ($showKeys) {
                $data = array_map(function ($data) use ($key) {
                    return sprintf("[%s]: %s", $key, $data);
                }, $data);
            }

            $errors = array_merge($errors, $data);
        }

        return join($separator, array_unique($errors));
    }

    public function getFlattenErrors()
    {
        $errors = [];

        foreach ($this->getErrors() as $data) {
            $errors = array_merge($errors, $data);
        }

        return array_unique($errors);
    }
}
