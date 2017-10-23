<?php
/**
 * Optime Consulting.
 * User: MAGUIRRE
 * Date: 18/10/2017
 * Time: 5:57 PM
 */

namespace Manuel\Bundle\UploadDataBundle\Validator\Constraint;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EntityExistsValidator
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class EntityExistsValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccesor;
    private $cachedData = [];

    /**
     * EntityExistsValidator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PropertyAccessorInterface $propertyAccesor
     */
    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccesor)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccesor = $propertyAccesor;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param EntityExists $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (null == $value || (is_string($value) && 0 === strlen(trim($value)))) {
            return;
        }

        if (!$this->isDataLoaded($constraint)) {
            $this->loadData($constraint);
        }

        $notifySuccess = $constraint->useSuccess();

        if ($this->isValidFromCache($constraint, $value)) {
            if ($notifySuccess) {
                $item = $this->getValidIemFromCache($constraint, $value);
                $this->notifySuccessItem($constraint, $value, $item);
            }

            return;

        } elseif ($this->isInvalidFromCache($constraint, $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->addViolation();

            return;
        }

        if ($item = $this->findItemByValue($constraint, $value)) {
            $this->addValidValueToCache($constraint, $value, $item);
            $notifySuccess and $this->notifySuccessItem($constraint, $value, $item);
        } else {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->addViolation();

            $this->addInvalidValueToCache($constraint, $value);
        }
    }

    private function notifySuccessItem(EntityExists $constraint, $value, $item)
    {
        $constraint->callSuccess($item, $value);
    }

    private function findItemByValue(EntityExists $constraint, $value)
    {
        $comparator = $constraint->useComparator()
            ? [$constraint, 'callComparator']
            : $this->createDefaultComparator($constraint->property);

        foreach ($this->getData($constraint) as $item) {
            if ($comparator($item, $value)) {
                return $item;
            }
        }
    }

    private function constraintUniqueId(EntityExists $object)
    {
        return spl_object_hash($object);
    }

    private function isDataLoaded(EntityExists $object)
    {
        return array_key_exists($this->constraintUniqueId($object), $this->cachedData);
    }

    private function loadData(EntityExists $constraint)
    {
        $repository = $this->entityManager->getRepository($constraint->class);

        if ($constraint->useQueryBuilder()) {
            $data = $constraint->callQueryBuilder($repository)->getQuery()->getResult();
        } else {
            $data = $repository->findAll();
        }

        $id = $this->constraintUniqueId($constraint);

        $this->cachedData[$id]['data'] = $data;
        $this->cachedData[$id]['invalids'] = [];
        $this->cachedData[$id]['valids'] = [];
    }

    /**
     * @param $property
     * @return \Closure
     */
    private function createDefaultComparator($property)
    {
        return function ($item, $value) use ($property) {
            return $this->propertyAccesor->getValue($item, $property) == $value;
        };
    }

    private function getData(EntityExists $constraint)
    {
        return $this->cachedData[$this->constraintUniqueId($constraint)]['data'];
    }

    private function addInvalidValueToCache(EntityExists $constraint, $value)
    {
        $this->cachedData[$this->constraintUniqueId($constraint)]['invalids'][$value] = true;
    }

    private function addValidValueToCache(EntityExists $constraint, $value, $item)
    {
        $this->cachedData[$this->constraintUniqueId($constraint)]['valids'][$value] = item;
    }

    private function isValidFromCache(EntityExists $constraint, $value)
    {
        return isset($this->cachedData[$this->constraintUniqueId($constraint)]['valids'][$value]);
    }

    private function getValidIemFromCache(EntityExists $constraint, $value)
    {
        return $this->cachedData[$this->constraintUniqueId($constraint)]['valids'][$value];
    }

    private function isInvalidFromCache(EntityExists $constraint, $value)
    {
        return isset($this->cachedData[$this->constraintUniqueId($constraint)]['invalids'][$value]);
    }
}