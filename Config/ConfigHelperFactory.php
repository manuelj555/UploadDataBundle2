<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Knp\Component\Pager\PaginatorInterface;
use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;

/**
 * @author Manuel Aguirre
 */
class ConfigHelperFactory
{
    /**
     * @var UploadRepository
     */
    private $repository;
    /**
     * @var PaginatorInterface|null
     */
    private $paginator;
    /**
     * @var ConfigProvider
     */
    private $provider;

    public function __construct(
        ConfigProvider $provider,
        UploadRepository $repository,
        ?PaginatorInterface $paginator = null
    ) {
        $this->repository = $repository;
        $this->paginator = $paginator;
        $this->provider = $provider;
    }

    public function create(UploadConfig $config): ConfigHelper
    {
        return new ConfigHelper(
            $config,
            $this->repository,
            $this->paginator
        );
    }

    public function createForType(string $type, array $options = []): ConfigHelper
    {
        return $this->create($this->provider->get($type, $options));
    }
}