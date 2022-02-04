<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use function is_array;
use function strtolower;

#[ORM\Table("upload_data_upload_attribute")]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
#[UniqueEntity(fields: ["name", "upload"])]
class UploadAttribute
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Upload::class, inversedBy: "attributes")]
    private Upload $upload;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $value;

    public function __construct(
        Upload $upload,
        string $name,
        mixed $value
    ) {
        $this->upload = $upload;
        $this->name = strtolower($name);
        $this->setValue($value);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function __toString()
    {
        $value = $this->getValue();

        return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value;
    }

}
