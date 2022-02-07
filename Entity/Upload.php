<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Data\ColumnsMatchInfo;
use function array_filter;
use function in_array;
use function is_a;

#[ORM\Table("upload_data_upload")]
#[ORM\Entity(repositoryClass: UploadRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
class Upload
{
    const STATUS_NOT_COMPLETE = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETE = 2;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $columnsMatch;
    /**
     * @var ?string Nombre del archivo original que se cargó
     */
    #[ORM\Column(nullable: true)]
    private ?string $filename;

    /**
     * @var ?string Nombre y Ruta del archivo procesado y renombrado por el sistema
     */
    #[ORM\Column(name: "full_filename", nullable: true)]
    private ?string $fullFilename;

    #[ORM\OneToMany(targetEntity: UploadedItem::class, mappedBy: "upload")]
    private iterable|Collection $items;

    /**
     * @var ?string Nombre corto del archivo procesado y renombrado por el sistema
     */
    #[ORM\Column(nullable: true)]
    private ?string $file;

    #[ORM\Column]
    private string $configClass;

    #[ORM\Column(nullable: true)]
    private ?int $valids;

    #[ORM\Column(nullable: true)]
    private ?int $invalids;

    #[ORM\Column(nullable: true)]
    private ?int $total;

    #[ORM\Column(name: "uploaded_at", nullable: true)]
    private ?DateTimeImmutable $uploadedAt;

    #[ORM\OneToMany(
        mappedBy: "upload",
        targetEntity: UploadAttribute::class,
        cascade: ["all"],
        fetch: "EAGER",
        orphanRemoval: true,
    )]
    private iterable|Collection $attributes;

    #[ORM\OneToMany(
        mappedBy: "upload",
        targetEntity: UploadAction::class,
        cascade: ["all"],
        fetch: "EAGER",
        orphanRemoval: true,
    )]
    private iterable|Collection $actions;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColumnsMatch(): ?array
    {
        return $this->columnsMatch;
    }

    public function isColumnsMatched(): bool
    {
        return null !== $this->columnsMatch;
    }

    public function setColumnsMatch(ColumnsMatchInfo $matchInfo): void
    {
        $matchInfo->validate();

        $this->columnsMatch = $matchInfo->getMatchedColumns();
    }

    public function getConfigClass(): string
    {
        return $this->configClass;
    }

    public function setConfigClass(string $configClass): void
    {
        if (!is_a($configClass, UploadConfig::class, true)) {
            throw new InvalidArgumentException(sprintf(
                "El parametro conflig class debe ser una instancia de '%s', pero llegó '%s'",
                UploadConfig::class,
                $configClass,
            ));
        }

        $this->configClass = $configClass;
    }

    public function isReadable(): bool
    {
        return $this->getUploadedAt() !== null
            and $this->isColumnsMatched()
            and $this->getAction('read')->isNotComplete()
            and $this->getAction('validate')->isNotComplete()
            and $this->getAction('transfer')->isNotComplete();
    }

    public function getUploadedAt(): ?DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function getAction(string $name): ?UploadAction
    {
        $name = strtolower($name);

        foreach ($this->getActions() as $action) {
            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }

    public function getActions(): iterable|ArrayCollection|Collection
    {
        return $this->actions;
    }

    public function isValidatable(): bool
    {
        return $this->getUploadedAt() !== null
            and $this->getAction('read')->isComplete()
            and !$this->getAction('validate')->isInProgress()
            and $this->getAction('transfer')->isNotComplete();
    }

    public function isTransferable(): bool
    {
        return $this->getUploadedAt() !== null
            and $this->getAction('read')->isComplete()
            and $this->getAction('validate')->isComplete()
            and $this->getAction('transfer')->isNotComplete()
            and $this->getValids() > 0;
    }

    public function isDefaultAction(string $name): bool
    {
        return in_array($name, ['transfer', 'read', 'validate', 'delete']);
    }

    public function canExecuteDefaultAction(string $name): bool
    {
        if ($name == 'transfer') {
            return $this->isTransferable();
        }

        if ($name == 'read') {
            return $this->isReadable();
        }

        if ($name == 'validate') {
            return $this->isValidatable();
        }

        if ($name == 'delete') {
            return $this->isDeletable();
        }

        return false;
    }

    public function getValids(): ?int
    {
        return $this->valids;
    }

    public function setValids(int $valid): void
    {
        $this->valids = $valid;
    }

    public function isDeletable(): bool
    {
        return $this->getAction('transfer')->isNotComplete();
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->uploadedAt = new DateTimeImmutable('now');

        $this->addAction(new UploadAction($this, 'read'));
        $this->addAction(new UploadAction($this, 'validate'));
        $this->addAction(new UploadAction($this, 'transfer'));
        $this->addAction(new UploadAction($this, 'delete'));
    }

    private function addAction(UploadAction $actions)
    {
        $this->actions[] = $actions;
    }

    public function addItem(array $data, int $rowNumber): UploadedItem
    {
        $this->items[] = $item = new UploadedItem($this, $data, $rowNumber);

        return $item;
    }

    public function getFullFilename(): ?string
    {
        return $this->fullFilename;
    }

    public function setFullFilename(string $fullFilename): void
    {
        $this->fullFilename = $fullFilename;
    }

    public function getInvalids(): ?int
    {
        return $this->invalids;
    }

    public function setInvalids(int $invalids): void
    {
        $this->invalids = $invalids;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function removeAction(UploadAction $actions): void
    {
        $this->actions->removeElement($actions);
    }

    public function setAttributeValue(string $name, $value): void
    {
        if ($attr = $this->getAttribute($name)) {
            $attr->setValue($value);
        } else {
            $this->attributes[] = new UploadAttribute($this, $name, $value);
        }
    }

    public function setAttributes(iterable $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttributeValue($key, $value);
        }
    }

    public function getAttributeValue(string $name): mixed
    {
        return $this->getAttribute($name)?->getValue();
    }

    public function getAttribute(string $name): ?UploadAttribute
    {
        $name = strtolower($name);

        foreach ($this->getAttributes() as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        return null;
    }

    public function getAttributes(): iterable|ArrayCollection|Collection
    {
        return $this->attributes;
    }

    public function getValidItems(): iterable|ArrayCollection|Collection
    {
        return $this->getItems()
            ->filter(function (UploadedItem $item) {
                return $item->getValid();
            });
    }

    public function getItems(): iterable|ArrayCollection|Collection
    {
        return $this->items;
    }

    public function getInvalidItems(): iterable|ArrayCollection|Collection
    {
        return $this->getItems()
            ->filter(function (UploadedItem $item) {
                return !$item->getValid();
            });
    }

    public function hasInProgressActions(): bool
    {
        /** @var UploadAction $action */
        foreach ($this->getActions() as $action) {
            if ($action->isInProgress()) {
                return true;
            }
        }

        return false;
    }

    public function getReadOptions(): array
    {
        return [
            'row_headers' => $this->getAttributeValue('row_headers') ?? 1,
            'columns_mapping' => array_filter($this->getColumnsMatch() ?? []),
        ];
    }
}
