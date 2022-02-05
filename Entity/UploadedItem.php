<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuel\Bundle\UploadDataBundle\Validator\GroupedConstraintViolations;

#[ORM\Table("upload_data_uploaded_item")]
#[ORM\Entity(repositoryClass: UploadedItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
class UploadedItem implements \ArrayAccess
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Upload::class, inversedBy: "items")]
    private Upload $upload;

    #[ORM\Column(name: "file_row_number", nullable: true)]
    private ?int $fileRowNumber;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $extras;

    #[ORM\Column(type: 'json', nullable: true)]
    private null|array|GroupedConstraintViolations $errors = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valid;

    private bool $hasDefaultErrors = false;

    public function __construct(Upload $upload, array $data, int $rowNumber)
    {
        $this->upload = $upload;
        $this->data = $data;
        $this->fileRowNumber = $rowNumber;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setErrors(GroupedConstraintViolations|array $errors): void
    {
        if (!($errors instanceof GroupedConstraintViolations)) {
            $errors = GroupedConstraintViolations::fromArray($errors);
        }

        $this->errors = $errors;
    }

    public function getErrors(): GroupedConstraintViolations
    {
        return $this->errors;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
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
        return $this->get($offset);
    }

    public function get(string $key): ?string
    {
        return $this->data[$key] ?? null;
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

    public function getExtras(): array
    {
        return $this->extras ?? [];
    }

    public function setExtras(array $extras): void
    {
        $this->extras = $extras;
    }

    public function setExtra($key, $value): void
    {
        $this->extras ??= [];
        $this->extras[$key] = $value;
    }

    public function getExtra($key)
    {
        return $this->extras[$key] ?? null;
    }

    public function getFileRowNumber(): int
    {
        return $this->fileRowNumber;
    }

    public function getErrorsAsString(string $separator = ', ', bool $showKeys = false, bool $allGroups = false): string
    {
        return $this->getErrors()->toString($separator, $showKeys, $allGroups);
    }

    public function getGroupErrorsAsString(string $group, string $separator = ', ', bool $showKeys = false): string
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

    public function getFlattenErrors(): array
    {
        return $this->getErrors()->getErrorsAsSimpleFormat();
    }

    public function hasDefaultErrors(): bool
    {
        return $this->hasDefaultErrors;
    }

    public function setHasDefaultErrors(bool $hasDefaultErrors = true): void
    {
        $this->hasDefaultErrors = $hasDefaultErrors;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function convertErrorsToArray()
    {
        if ($this->errors instanceof GroupedConstraintViolations) {
            $this->errors = $this->errors->getAll();
        }
    }

    #[ORM\PostLoad]
    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function convertErrorsToObject()
    {
        $this->setErrors($this->errors ?? []);
    }
}
