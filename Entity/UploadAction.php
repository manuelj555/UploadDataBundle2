<?php

namespace Manuelj555\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UploadAction
 *
 * @ORM\Table(name="upload_data_upload_action")
 * @ORM\Entity
 */
class UploadAction
{
    const STATUS_NOT_COMPLETE = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETE = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Manuelj555\Bundle\UploadDataBundle\Entity\Upload", inversedBy="actions")
     */
    private $upload;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completedAt", type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    function __construct($name = null, $position = 0)
    {
        $this->setName($name);
        $this->position = $position;
        $this->setStatus(self::STATUS_NOT_COMPLETE);
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
     * Set upload
     *
     * @param \stdClass $upload
     *
     * @return UploadAction
     */
    public function setUpload($upload)
    {
        $this->upload = $upload;

        return $this;
    }

    /**
     * Get upload
     *
     * @return \stdClass
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return UploadAction
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
     * Set status
     *
     * @param integer $status
     *
     * @return UploadAction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set completedAt
     *
     * @param \DateTime $completedAt
     *
     * @return UploadAction
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get completedAt
     *
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return UploadAction
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function isNotComplete()
    {
        return $this->getStatus() == self::STATUS_NOT_COMPLETE;
    }

    public function isComplete()
    {
        return $this->getStatus() == self::STATUS_COMPLETE;
    }

    public function isInProgress()
    {
        return $this->getStatus() == self::STATUS_IN_PROGRESS;
    }

    public function setComplete()
    {
        $this->setStatus(self::STATUS_COMPLETE);
        $this->setCompletedAt(new \DateTime());
    }

    public function setInProgress()
    {
        $this->setStatus(self::STATUS_IN_PROGRESS);
    }

    public function setNotComplete()
    {
        $this->setStatus(self::STATUS_NOT_COMPLETE);
        $this->setCompletedAt(null);
    }
}
