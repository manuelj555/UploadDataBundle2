<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Twig\Extention\Runtime;

use Manuel\Bundle\UploadDataBundle\Config\ConfigHelperFactory;
use Manuel\Bundle\UploadDataBundle\Config\ResolvedUploadConfig;
use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Manuel Aguirre
 */
class UploadRuntime implements RuntimeExtensionInterface
{
    private array $cachedColTitles = [];
    private array $cachedCols = [];
    private array $cachedCompletedActions = [];
    private array $cachedActionableActions = [];

    public function __construct(
        private ConfigProvider $configProvider,
        private ConfigHelperFactory $helperFactory,
        private RequestStack $requestStack,
    ) {
    }

    public function isTransferred(Upload $upload): bool
    {
        return $this->isActionCompleted($upload, 'transfer');
    }

    public function isValidated(Upload $upload): bool
    {
        return $this->isActionCompleted($upload, 'validate');
    }

    public function isRead(Upload $upload): bool
    {
        return $this->isActionCompleted($upload, 'read');
    }

    public function isActionCompleted(Upload $upload, string $actionName): bool
    {
        return $upload->getAction($actionName)?->isComplete() ?? false;
    }

    public function isActionActionable(Upload $upload, string $actionName): bool
    {
        $config = $this->configProvider->get($upload->getConfigClass());

        return $config->getConfig()->isActionable($upload, $actionName);
    }

    public function getColumns(Upload|string $uploadOrConfigClass): array
    {
        return $this->cachedCols[$this->resolveConfigType($uploadOrConfigClass)] ??= $this
            ->getResolvedConfig($uploadOrConfigClass)
            ->getConfigColumns()
            ->getColumnNames();
    }

    public function getColumnTitles(Upload|string $uploadOrConfigClass): array
    {
        return $this->cachedColTitles[$this->resolveConfigType($uploadOrConfigClass)] ??= $this
            ->getResolvedConfig($uploadOrConfigClass)
            ->getConfigColumns()
            ->getColumnsWithLabels();
    }

    public function getRows(Upload $upload): iterable
    {
        return $this->helperFactory
            ->create($this->getResolvedConfig($upload))
            ->show($upload, $this->requestStack->getCurrentRequest());
    }

    public function getItemValue(UploadedItem $item, string $key): ?string
    {
        return $item->get($key);
    }

    private function getResolvedConfig(Upload|string $uploadOrConfigClass): ResolvedUploadConfig
    {
        return $this->configProvider->get($this->resolveConfigType($uploadOrConfigClass));
    }

    private function resolveConfigType(Upload|string $uploadOrConfigClass): string
    {
        return $uploadOrConfigClass instanceof Upload
            ? $uploadOrConfigClass->getConfigClass()
            : $uploadOrConfigClass;
    }
}