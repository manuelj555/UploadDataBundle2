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
    public function __construct(
        private ConfigProvider $provider,
        private UploadConfigHandler $configHandler,
        private ExcelHeadersMatcher $headersMatcher,
        private UploadRepository $repository,
        private UploadedItemRepository $itemRepository,
        private EntityManagerInterface $entityManager,
        private ?PaginatorInterface $paginator = null,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function create(ResolvedUploadConfig $resolvedConfig): ConfigHelper
    {
        return new ConfigHelper(
            $resolvedConfig,
            $this->entityManager,
            $this->configHandler,
            $this->headersMatcher,
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