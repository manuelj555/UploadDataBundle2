<?php

namespace Manuel\Bundle\UploadDataBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * UploadedItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UploadedItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadedItem::class);
    }

    public function getQueryByUpload($upload, $filters = array())
    {
        $query = $this->createQueryBuilder('uploaded_item')
            ->where('uploaded_item.upload = :upload')
            ->setParameter('upload', $upload);

        if (array_key_exists('valid', $filters)) {
            $query->andWhere('uploaded_item.isValid = :is_valid')
                ->setParameter('is_valid', (bool)$filters['valid']);
        }

        if (array_key_exists('extra', $filters)) {
            $query->andWhere('uploaded_item.extras LIKE :extra')
                ->setParameter('extra', '%' . $filters['extra'] . '%');
        }

        if (array_key_exists('status', $filters)) {
            $query->andWhere('uploaded_item.status = :status')
                ->setParameter('status', $filters['status']);
        }

        return $query;
    }
}
