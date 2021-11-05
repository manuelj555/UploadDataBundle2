<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuel\Bundle\UploadDataBundle\Validator\GroupedConstraintViolations;

/**
 * UploadedItem
 *
 * @ORM\Table(name="upload_data_uploaded_item")
 * @ORM\Entity(repositoryClass="Manuel\Bundle\UploadDataBundle\Entity\UploadedItemRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
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
     * @ORM\Column(name="data", type="json", nullable=true)
     */
    private $data;

    /**
     * @var array
     *
     * @ORM\Column(name="extras", type="json", nullable=true)
     */
    private $extras = array();

    /**
     * @var array
     *
     * @ORM\Column(name="errors", type="json", nullable=true)
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
     * @var bool
     */
    private $hasDefaultErrors = false;

    public function __construct(Upload $upload, array $data)
    {
        $this->upload = $upload;
        $this->setData($data);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setErrors($errors): void
    {
        if (!($errors instanceof GroupedConstraintViolations)) {
            $errors = GroupedConstraintViolations::fromArray($errors);
        }

        $this->errors = $errors;
    }

    /**
     * @param null $group
     * @param bool $grouped
     * @return GroupedConstraintViolations|array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    public function isValidForGroup($name): bool
    {
        return !$this->getErrors()->hasViolationsForGroup($name);
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function setExtras(array $extras)
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

    public function getErrorsAsString($separator = ', ', $showKeys = false, $allGroups = false)
    {
        $errors = [];
        $group = $allGroups ? null : 'default';

        foreach ($this->getErrors()->getAll($group, false) as $columnName => $data) {
            if ($showKeys) {
                $data = array_map(function ($data) use ($columnName) {
                    return sprintf("[%s]: %s", $columnName, $data);
                }, $data);
            }

            $errors = array_merge($errors, $data);
        }

        return join($separator, array_unique($errors));
    }

    public function getGroupErrorsAsString($group, $separator = ', ', $showKeys = false)
    {
        $errors = [];

        foreach ($this->getErrors()->getAll($group) as $columnName => $data) {
            if ($showKeys) {
                $data = array_map(function ($data) use ($columnName) {
                    return sprintf("[%s]: %s", $columnName, $data);
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

    public function hasDefaultErrors(): bool
    {
        return $this->hasDefaultErrors;
    }

    public function setHasDefaultErrors(bool $hasDefaultErrors = true): void
    {
        $this->hasDefaultErrors = $hasDefaultErrors;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function convertErrorsToArray()
    {
        if ($this->errors instanceof GroupedConstraintViolations) {
            $this->errors = $this->errors->getAll();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * @ORM\PostLoad()
     */
    public function convertErrorsToObject()
    {
        if (!($this->errors instanceof GroupedConstraintViolations)) {
            $this->errors = GroupedConstraintViolations::fromArray($this->errors ?: []);
        }
    }
}
