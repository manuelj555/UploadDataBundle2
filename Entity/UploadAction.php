<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * UploadAction
 *
 * @ORM\Table(name="upload_data_upload_action")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
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
     * @ORM\ManyToOne(targetEntity="Manuel\Bundle\UploadDataBundle\Entity\Upload", inversedBy="actions")
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
     * @var bool
     *
     * @ORM\Column(name="completed", type="boolean")
     */
    private $actionCompleted = null;

    function __construct(Upload $upload, $name = null)
    {
        $this->upload = $upload;
        $this->setName($name);
        $this->setStatus(self::STATUS_NOT_COMPLETE);
        $this->actionCompleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function setName(string $name): void
    {
        $this->name = strtolower($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;

        if ($status == self::STATUS_COMPLETE) {
            $this->actionCompleted = true;
        }
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setCompletedAt($completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function isNotComplete(): bool
    {
        return $this->getStatus() == self::STATUS_NOT_COMPLETE;
    }

    public function isComplete($checkPreviousCompleted = true): bool
    {
        if ($checkPreviousCompleted && $this->actionCompleted) {
            return true;
        }

        return ($this->getStatus() == self::STATUS_COMPLETE);
    }

    public function isInProgress(): bool
    {
        return $this->getStatus() == self::STATUS_IN_PROGRESS;
    }

    public function setComplete(): void
    {
        $this->setStatus(self::STATUS_COMPLETE);
        $this->actionCompleted = true;
        $this->setCompletedAt(new \DateTime());
    }

    public function setInProgress(): void
    {
        $this->setStatus(self::STATUS_IN_PROGRESS);
    }

    public function setNotComplete(): void
    {
        $this->setStatus(self::STATUS_NOT_COMPLETE);
        $this->setCompletedAt(null);
    }

    /**
     * @ORM\PostLoad()
     */
    public function load(): void
    {
        if (!$this->actionCompleted && ($this->status == self::STATUS_COMPLETE)) {
            $this->actionCompleted = true;
        }
    }

}
