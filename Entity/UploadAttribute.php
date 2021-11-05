<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use function is_array;

/**
 * UploadAttribute
 *
 * @ORM\Table(name="upload_data_upload_attribute")
 * @ORM\Entity
 * @UniqueEntity(fields={"name", "upload"})
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class UploadAttribute
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
     * @ORM\ManyToOne(targetEntity="Manuel\Bundle\UploadDataBundle\Entity\Upload", inversedBy="attributes")
     */
    private $upload;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    private $formLabel;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="json", nullable=true)
     */
    private $value;

    public function __construct(Upload $upload, $name = null, $value = null)
    {
        $this->upload = $upload;
        $this->setName($name);
        $this->setValue($value);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = strtolower($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setFormLabel(string $formLabel): void
    {
        $this->formLabel = $formLabel;
    }

    public function getFormLabel(): ?string
    {
        return $this->formLabel;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function getLabel() :?string
    {
        return $this->label;
    }

    public function __toString()
    {
        $value = $this->getValue();

        return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value;
    }

}
