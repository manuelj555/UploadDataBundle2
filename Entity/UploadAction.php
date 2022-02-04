<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use function strtolower;

#[ORM\Table("upload_data_upload_action")]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
class UploadAction
{
    const STATUS_NOT_COMPLETE = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETE = 2;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Upload::class, inversedBy: "actions")]
    private Upload $upload;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: "smallint")]
    private int $status;

    #[ORM\Column(name: "completed_at", nullable: true)]
    private ?DateTimeImmutable $completedAt;

    #[ORM\Column(name: "completed", nullable: true)]
    private ?bool $actionCompleted;

    public function __construct(Upload $upload, string $name)
    {
        $this->upload = $upload;
        $this->name = strtolower($name);
        $this->setStatus(self::STATUS_NOT_COMPLETE);
        $this->actionCompleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCompletedAt(): ?DateTimeImmutable
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
        $this->completedAt = new DateTimeImmutable();
    }

    public function setInProgress(): void
    {
        $this->setStatus(self::STATUS_IN_PROGRESS);
    }

    public function setNotComplete(): void
    {
        $this->setStatus(self::STATUS_NOT_COMPLETE);
        $this->completedAt = null;
    }

    #[ORM\PostLoad]
    public function load(): void
    {
        if (!$this->actionCompleted && ($this->status == self::STATUS_COMPLETE)) {
            $this->actionCompleted = true;
        }
    }

}
