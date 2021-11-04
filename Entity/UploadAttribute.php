<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="is_array", type="boolean", nullable=true)
     */
    private $isArray = 0;

    function __construct($name = null, $value = null)
    {
        $this->setName($name);
        $this->value = $value;
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return UploadAttribute
     */
    public function setName($name)
    {
        $this->name = strtolower($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return UploadAttribute
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set upload
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\Upload $upload
     *
     * @return UploadAttribute
     */
    public function setUpload(\Manuel\Bundle\UploadDataBundle\Entity\Upload $upload = null)
    {
        $this->upload = $upload;

        return $this;
    }

    /**
     * Get upload
     *
     * @return \Manuel\Bundle\UploadDataBundle\Entity\Upload
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * Set formLabel
     *
     * @param string $formLabel
     *
     * @return UploadAttribute
     */
    public function setFormLabel($formLabel)
    {
        $this->formLabel = $formLabel;

        return $this;
    }

    /**
     * Get formLabel
     *
     * @return string
     */
    public function getFormLabel()
    {
        return $this->formLabel;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return UploadAttribute
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set isArray
     *
     * @param boolean $isArray
     *
     * @return UploadAttribute
     */
    public function setIsArray($isArray)
    {
        $this->isArray = $isArray;

        return $this;
    }

    /**
     * Get isArray
     *
     * @return boolean
     */
    public function getIsArray()
    {
        return $this->isArray;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function arrayToString()
    {
        if (is_array($this->value)) {
            $this->setIsArray(true);
            $this->setValue(serialize($this->value));
        }
    }

    /**
     * @ORM\PostLoad()
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function stringToArray()
    {
        if ($this->isArray) {
            $this->setValue(unserialize($this->getValue()));
        }
    }

    public function __toString()
    {
        $value = $this->getValue();

        return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value;
    }


}
