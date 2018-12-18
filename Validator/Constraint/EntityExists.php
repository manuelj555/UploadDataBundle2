<?php

namespace Manuel\Bundle\UploadDataBundle\Validator\Constraint;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Component\Validator\Constraint;

/**
 * Class EntityExists
 *
 * @Annotation
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class EntityExists extends Constraint
{
    public $message = 'Item not found';

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $property;

    /**
     * @var \Closure
     */
    public $query_builder;

    /**
     * @var int
     */
    public $hydrate = Query::HYDRATE_OBJECT;

    /**
     * @var \Closure
     */
    public $comparator;

    /**
     * @var \Closure
     */
    public $success;

    /**
     * Funcion encargada de crear un registro que no exista.
     *
     * @var \Closure|null
     */
    public $create_factory;

    public function getRequiredOptions()
    {
        return [
            'class',
            'property',
        ];
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'upload_data.entity_exists';
    }

    public function callQueryBuilder(EntityRepository $er)
    {
        return call_user_func($this->query_builder, $er);
    }

    public function callComparator($entity, $value)
    {
        return call_user_func($this->comparator, $entity, $value, $this->property);
    }

    public function callCreateFactory(UploadedItem $item, $value)
    {
        return call_user_func($this->create_factory, $item, $value, $this->property);
    }

    public function callSuccess($entity, UploadedItem $item, $value)
    {
        return call_user_func($this->success, $entity, $item, $value, $this->property);
    }

    public function useQueryBuilder()
    {
        return $this->query_builder instanceof \Closure;
    }

    public function useComparator()
    {
        return $this->comparator instanceof \Closure;
    }

    public function useSuccess()
    {
        return $this->success instanceof \Closure;
    }

    public function canCreate()
    {
        return  $this->create_factory instanceof \Closure;
    }
}