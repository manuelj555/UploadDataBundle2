<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Upload
 *
 * @ORM\Table(name="upload_data_upload")
 * @ORM\Entity(repositoryClass="Manuel\Bundle\UploadDataBundle\Entity\UploadRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Upload
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
     * @var string Nombre del archivo original que se cargó
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var string Nombre y Ruta del archivo procesado y renombrado por el sistema
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fullFilename;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Manuel\Bundle\UploadDataBundle\Entity\UploadedItem", mappedBy="upload", orphanRemoval=true)
     */
    private $items;

    /**
     * @var string Nombre corto del archivo procesado y renombrado por el sistema
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valids;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invalids;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uploadedAt", type="datetime", nullable=true)
     */
    private $uploadedAt;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute",
     *      cascade={"all"},
     *      mappedBy="upload",
     *      fetch="EAGER",
     *      orphanRemoval=true
     * )
     */
    private $attributes;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Manuel\Bundle\UploadDataBundle\Entity\UploadAction",
     *      cascade={"all"},
     *      mappedBy="upload",
     *      fetch="EAGER",
     *      orphanRemoval=true,
     * )
     */
    private $actions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Upload
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function isReadable()
    {
        return $this->getUploadedAt() !== null
            and $this->getAction('read')->isNotComplete()
            and $this->getAction('validate')->isNotComplete()
            and $this->getAction('transfer')->isNotComplete();
    }

    /**
     * Get uploadedAt
     *
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    /**
     * Set uploadedAt
     *
     * @param \DateTime $uploadedAt
     *
     * @return Upload
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * @param $name
     *
     * @return UploadAction|null
     */
    public function getAction($name)
    {
        $name = strtolower($name);

        foreach ($this->getActions() as $action) {
            if ($action->getName() === $name) {
                return $action;
            }
        }
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    public function isValidatable()
    {
        return $this->getUploadedAt() !== null
            and $this->getAction('read')->isComplete()
            and !$this->getAction('validate')->isInProgress()
            and $this->getAction('transfer')->isNotComplete();
    }

    public function isTransferable()
    {
        return $this->getUploadedAt() !== null
            and $this->getAction('read')->isComplete()
            and $this->getAction('validate')->isComplete()
            and $this->getAction('transfer')->isNotComplete()
            and $this->getValids() > 0;
    }

    /**
     * Get valids
     *
     * @return integer
     */
    public function getValids()
    {
        return $this->valids;
    }

    /**
     * Set valids
     *
     * @param integer $valids
     *
     * @return Upload
     */
    public function setValids($valids)
    {
        $this->valids = $valids;

        return $this;
    }

    public function isDeletable()
    {
        return $this->getAction('transfer')->isNotComplete();
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return Upload
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Upload
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->setUploadedAt(new \DateTime());

        $this->addAction(new UploadAction($this, 'read'));
        $this->addAction(new UploadAction($this, 'validate'));
        $this->addAction(new UploadAction($this, 'transfer'));
        $this->addAction(new UploadAction($this, 'delete'));
    }

    private function addAction(UploadAction $actions)
    {
        $this->actions[] = $actions;
    }

    public function addItem(array $data): UploadedItem
    {
        $this->items[] = $item = new UploadedItem($this, $data);

        return $item;
    }

    /**
     * Get fullFilename
     *
     * @return string
     */
    public function getFullFilename()
    {
        return $this->fullFilename;
    }

    /**
     * Set fullFilename
     *
     * @param string $fullFilename
     *
     * @return Upload
     */
    public function setFullFilename($fullFilename)
    {
        $this->fullFilename = $fullFilename;

        return $this;
    }

    /**
     * Get invalids
     *
     * @return integer
     */
    public function getInvalids()
    {
        return $this->invalids;
    }

    /**
     * Set invalids
     *
     * @param integer $invalids
     *
     * @return Upload
     */
    public function setInvalids($invalids)
    {
        $this->invalids = $invalids;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return Upload
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param UploadAction $actions
     */
    public function removeAction(UploadAction $actions)
    {
        $this->actions->removeElement($actions);
    }

    public function setAttributeValue(string $name, $value)
    {
        if ($attr = $this->getAttribute($name)) {
            $attr->setValue($value);
        } else {
            $this->attributes[] = new UploadAttribute($this, $name, $value);
        }
    }

    /**
     * @param $name
     *
     * @return UploadAttribute|null
     */
    public function getAttribute(string $name)
    {
        $name = strtolower($name);

        foreach ($this->getAttributes() as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getValidItems()
    {
        return $this->getItems()
            ->filter(function (UploadedItem $item) {
                return $item->getIsValid();
            });
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    public function hasInProgressActions()
    {
        /** @var UploadAction $action */
        foreach ($this->getActions() as $action) {
            if ($action->isInProgress()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        if ($columns = $this->getAttributeValue('configured_columns')) {
            return $columns;
        }

        $fileNames = $this->getColumnNames(true);
        $expectedNames = $this->getColumnKeys();
        $columns = [];

        if ($expectedNames) {
            foreach ($expectedNames as $index => $name) {
                $key = isset($fileNames[$index]) ? $fileNames[$index] : ucfirst($name);

                $columns[$key] = $name;
            }
        }

        return $columns;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getAttributeValue($name)
    {
        if ($attr = $this->getAttribute($name)) {
            return $attr->getValue();
        }
    }

    /**
     * @return array|null
     */
    public function getColumnNames($all = false)
    {
        $config = $this->getAttributeValue('config_read');

        if (!isset($config['header_mapping'][1])) {
            return;
        }

        if ($all) {
            return $config['header_mapping'][1];
        } else {
            return array_intersect_key($config['header_mapping'][1], $config['header_mapping'][0]);
        }
    }

    /**
     * @return array|null
     */
    public function getColumnKeys()
    {
        $config = $this->getAttributeValue('config_read');

        if (!isset($config['header_mapping'][0])) {
            return;
        }

        return $config['header_mapping'][0];
    }
}
