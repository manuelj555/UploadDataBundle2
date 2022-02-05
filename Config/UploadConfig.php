<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;
use Manuel\Bundle\UploadDataBundle\Validator\GroupedConstraintViolations;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class UploadConfig
{
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    abstract public function configureColumns(array $options): ConfigColumns;

    abstract public function transfer(Upload $upload): void;

    public function isActionable(Upload $upload, string $actionName): bool
    {
        if ($upload->isDefaultAction($actionName)) {
            return $upload->canExecuteDefaultAction($actionName);
        }

        if ($action = $upload->getAction($actionName)) {
            return $action->isNotComplete();
        }

        return false;
    }

    public function getQueryList(UploadRepository $repository, $filters = null, $order = 'DESC'): QueryBuilder
    {
        if (is_array($filters) and array_key_exists('search', $filters)) {
            $search = $filters['search'];
        } else {
            $search = null;
        }

        $queryBuilder = $repository->getQueryForType($this->getUploadType(), $search, $order);

        if ($this->excludeDeletedUploads()) {
            $this->addDeleteExclusionFilter($queryBuilder);
        }

        return $queryBuilder;
    }

    public function getUploadType(): string
    {
        return $this::class;
    }

    protected function excludeDeletedUploads(): bool
    {
        return true;
    }

    protected function addDeleteExclusionFilter(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->not(
                $queryBuilder->expr()->exists('
                SELECT act
                FROM UploadDataBundle:UploadAction act
                WHERE
                    act.upload = upload
                  AND
                    act.name = \'delete\'
                  AND
                    act.status = :action_status_completed
                ')
            )
        )
            ->setParameter('action_status_completed', Upload::STATUS_COMPLETE);
    }

    public function getInstance(): Upload
    {
        $upload = new Upload();
//        $upload->setAttributeValue('configured_columns');

        return $upload;
    }

    public function isAlreadyProcessedItemValid(UploadedItem $item): bool
    {
        return $item->getValid();
    }

    public function validateItem(UploadedItem $item, ContextualValidatorInterface $context, Upload $upload)
    {
    }

    /**
     * Determina cuando un item es considerado invalido y cuando es valido.
     *
     * Por defecto es invalido cuando hay errores de valicacion para la categoria|grupo por defecto.
     *
     * @param GroupedConstraintViolations $violations
     * @param UploadedItem $item
     * @return bool
     */
    public function shouldItemCanBeConsideredAsValid(
        GroupedConstraintViolations $violations,
        UploadedItem $item,
    ): bool {
        return !$violations->hasViolationsForGroup('default');
    }

    public function processAction(Upload $upload, UploadAction $action)
    {
    }

    public function itsAnExcludedItem(UploadedItem $item): bool
    {
        return false;
    }
}
