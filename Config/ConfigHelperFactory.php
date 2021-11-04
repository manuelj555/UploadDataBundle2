<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ExcelHeadersMatcher;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItemRepository;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Psr\Log\LoggerInterface;

/**
 * @author Manuel Aguirre
 */
class ConfigHelperFactory
{
    /**
     * @var ConfigProvider
     */
    private $provider;
    /**
     * @var ExcelHeadersMatcher
     */
    private $headersMatcher;
    /**
     * @var UploadRepository
     */
    private $repository;
    /**
     * @var PaginatorInterface|null
     */
    private $paginator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UploadedItemRepository
     */
    private $itemRepository;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(
        ConfigProvider $provider,
        ExcelHeadersMatcher $headersMatcher,
        UploadRepository $repository,
        UploadedItemRepository $itemRepository,
        EntityManagerInterface $entityManager,
        ?PaginatorInterface $paginator = null,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->headersMatcher = $headersMatcher;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        $this->provider = $provider;
        $this->paginator = $paginator;
        $this->logger = $logger;
    }

    public function create(UploadConfig $config): ConfigHelper
    {
        return new ConfigHelper(
            $config,
            $this->headersMatcher,
            $this->entityManager,
            $this->repository,
            $this->itemRepository,
            $this->paginator,
            $this->logger
        );
    }

    public function createForType(string $type, array $options = []): ConfigHelper
    {
        return $this->create($this->provider->get($type, $options));
    }
}