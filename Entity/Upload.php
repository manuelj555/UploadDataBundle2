<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Upload
 *
 * @ORM\Table(name="upload_data_upload")
 * @ORM\Entity(repositoryClass="Manuel\Bundle\UploadDataBundle\Entity\UploadRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var string
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
     * @var string
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

//    /**
//     * @var smallint
//     *
//     * @ORM\Column(type="smallint")
//     */
//    private $readed;
//
//    /**
//     * @var \DateTime
//     *
//     * @ORM\Column(name="readedAt", type="datetime", nullable=true)
//     */
//    private $readedAt;
//
//    /**
//     * @var smallint
//     *
//     * @ORM\Column(type="smallint")
//     */
//    private $validated;
//
//    /**
//     * @var \DateTime
//     *
//     * @ORM\Column(name="validatedAt", type="datetime", nullable=true)
//     */
//    private $validatedAt;
//
//    /**
//     * @var smallint
//     *
//     * @ORM\Column(type="smallint")
//     */
//    private $transfered;
//
//    /**
//     * @var \DateTime
//     *
//     * @ORM\Column(name="transferedAt", type="datetime", nullable=true)
//     */
//    private $transferedAt;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute",
     *      cascade={"all"},
     *      mappedBy="upload",
     *      orphanRemoval=true
     * )
     */
    private $attributes;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Manuel\Bundle\UploadDataBundle\Entity\UploadAction",
     *      cascade={"all"},
     *      mappedBy="upload",
     *      orphanRemoval=true,
     * )
     * @ORM\OrderBy(value={"position": "ASC"})
     */
    private $actions;

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
     * Get uploadedAt
     *
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    public function isReadable()
    {
        return $this->getUploadedAt() !== null
        and $this->getAction('read')->isNotComplete()
        and $this->getAction('validate')->isNotComplete()
        and $this->getAction('transfer')->isNotComplete();
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

    public function isDeletable()
    {
        return $this->getAction('transfer')->isNotComplete();
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
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
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
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->setUploadedAt(new \DateTime());
//        $this->setReaded(self::STATUS_NOT_COMPLETE);
//        $this->setValidated(self::STATUS_NOT_COMPLETE);
//        $this->setTransfered(self::STATUS_NOT_COMPLETE);

        $this->addAction(new UploadAction('read', 0));
        $this->addAction(new UploadAction('validate', 100));
        $this->addAction(new UploadAction('transfer', 200));
        $this->addAction(new UploadAction('delete', 1000));
    }

    /**
     * Add items
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadedItem $items
     *
     * @return Upload
     */
    public function addItem(\Manuel\Bundle\UploadDataBundle\Entity\UploadedItem $items)
    {
        $this->items[] = $items;

        $items->setUpload($this);

        return $this;
    }

    /**
     * Remove items
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadedItem $items
     */
    public function removeItem(\Manuel\Bundle\UploadDataBundle\Entity\UploadedItem $items)
    {
        $this->items->removeElement($items);

        $items->setUpload(null);
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
     * Get fullFilename
     *
     * @return string
     */
    public function getFullFilename()
    {
        return $this->fullFilename;
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
     * Get invalids
     *
     * @return integer
     */
    public function getInvalids()
    {
        return $this->invalids;
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
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Add attributes
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute $attributes
     *
     * @return Upload
     */
    public function addAttribute(\Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute $attributes)
    {
        $this->attributes[] = $attributes;

        $attributes->setUpload($this);

        return $this;
    }

    /**
     * Remove attributes
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute $attributes
     */
    public function removeAttribute(\Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute $attributes)
    {
        $this->attributes->removeElement($attributes);

        $attributes->setUpload(null);
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

    /**
     * Add actions
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadAction $actions
     *
     * @return Upload
     */
    public function addAction(\Manuel\Bundle\UploadDataBundle\Entity\UploadAction $actions)
    {
        $this->actions[] = $actions;

        $actions->setUpload($this);

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \Manuel\Bundle\UploadDataBundle\Entity\UploadAction $actions
     */
    public function removeAction(\Manuel\Bundle\UploadDataBundle\Entity\UploadAction $actions)
    {
        $this->actions->removeElement($actions);

        $actions->setUpload(null);
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
     * @param $name
     *
     * @return UploadAttribute|null
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        foreach ($this->getAttributes() as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }
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

    public function setAttributeValue($name, $value)
    {
        if($attr = $this->getAttribute($name)){
            $attr->setValue($value);
        }else{
            $this->addAttribute($attr = new UploadAttribute($name, $value));
        }

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getValidItems()
    {
        return $this->getItems()
            ->filter(function (UploadedItem $item) {
                return $item->getIsValid();
            });
    }
}
