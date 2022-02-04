<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Manuel\Bundle\UploadDataBundle\Data\ColumnsMatchInfo;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ExcelHeadersMatcher;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItemRepository;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Manuel\Bundle\UploadDataBundle\Exception\UploadProcessException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Manuel Aguirre
 */
class ConfigHelper
{
    /**
     * @var Exception|null
     */
    private ?Exception $lastException;

    public function __construct(
        private ResolvedUploadConfig $resolvedConfig,
        private EntityManagerInterface $entityManager,
        private UploadConfigHandler $configHandler,
        private ExcelHeadersMatcher $headersMatcher,
        private UploadRepository $repository,
        private UploadedItemRepository $itemRepository,
        private ?PaginatorInterface $paginator = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getConfig(): UploadConfig
    {
        return $this->resolvedConfig->getConfig();
    }

    public function getListData(Request $request, array $filters = null)
    {
        $query = $this->getConfig()->getQueryList(
            $this->repository, $filters
        );

        return $this->paginateIfApply($query, $request);
    }

    public function upload(UploadedFile $uploadedFile, array $formData = [], array $uploadAttributes = []): Upload
    {
        return $this->configHandler->processUpload(
            $this->resolvedConfig,
            $uploadedFile,
            $formData,
            $uploadAttributes,
        );
    }

    public function read(Upload $upload, bool $throwOnFail = false): bool
    {
        try {
            return $this->configHandler->processRead($this->resolvedConfig, $upload);
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la validación del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            if ($throwOnFail) {
                throw $e;
            }
        }

        return false;
    }

    public function validate(Upload $upload, $onlyInvalids = false, bool $throwOnFail = false): bool
    {
        try {
            return $this->configHandler->processValidation($this->resolvedConfig, $upload, $onlyInvalids);
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la validación del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            if ($throwOnFail) {
                throw $e;
            }
        }

        return false;
    }

    public function transfer(Upload $upload, bool $throwOnFail = false): bool
    {
        try {
            return $this->configHandler->processTransfer($this->resolvedConfig, $upload);
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la transferencia del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            if ($throwOnFail) {
                throw $e;
            }
        }

        return false;
    }

    public function customAction(Upload $upload, string $action, bool $throwOnFail = false): bool
    {
        try {
            return $this->configHandler->processActionByName($this->resolvedConfig, $upload, $action);
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la acción {action} del excel', [
                    'action' => $action,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            if ($throwOnFail) {
                throw $e;
            }
        }

        return false;
    }

    public function processAll(Upload $upload, bool $preventTransferOnInvalid = true): bool
    {
        if (!$this->getConfig()->isActionable($upload, 'read')) {
            throw UploadProcessException::fromMessage(
                'Esta carga ya fué leida y no se puede volver a procesar',
                'process_all'
            );
        }

        if (null == $upload->getAttributeValue('config_read')) {
            $this->configureDefaultMatch($upload);
        }

        $this->read($upload, true);
        $this->validate($upload, false, true);

        if ($preventTransferOnInvalid && $upload->getInvalids() > 0) {
            return false;
        }

        $this->transfer($upload, true);

        return true;
    }

    public function show(Upload $upload, Request $request)
    {
        $query = $this->itemRepository->getQueryByUpload($upload, $request->query->all());

        return $this->paginateIfApply($query, $request);
    }

    public function delete(Upload $upload, bool $throwOnFail = false): bool
    {
        try {
            $this->configHandler->processDelete($this->resolvedConfig, $upload);

            return true;
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la eliminación del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            if ($throwOnFail) {
                throw $e;
            }
        }

        return false;
    }

    public function restoreInProgress(Upload $upload)
    {
        /** @var UploadAction $action */
        foreach ($upload->getActions() as $action) {
            if ($action->isInProgress()) {
                $action->setNotComplete();
                $this->entityManager->persist($action);
            }
        }

        $this->entityManager->flush();
    }

    public function getLastException(): ?Exception
    {
        return $this->lastException;
    }

    public function getDefaultMatchInfo(Upload $upload, array $options = []): ColumnsMatchInfo
    {
        return $this->headersMatcher->getDefaultMatchInfo($this->resolvedConfig, $upload, $options);
    }

    public function applyMatch(ColumnsMatchInfo $matchInfo, array $matchData): array
    {
        $match = $this->headersMatcher->applyMatch($this->resolvedConfig, $matchInfo, $matchData);

        $this->entityManager->persist($matchInfo->getUpload());
        $this->entityManager->flush();

        return $match;
    }

    /**
     * Este método es para cuando se quiere hacer un match automático en los procesos de carga,
     * es decir, que no se le quiere permitir al usuario hacer un match manual de las columnas
     * del excel.
     */
    public function configureDefaultMatch(Upload $upload, array $options = []): void
    {
        isset($options['row_headers']) || $options['row_headers'] = 1;

        $reader = $this->readerLoader->get($upload->getFullFilename());
        $headers = $reader->getRowHeaders($upload->getFullFilename(), $options);
        $mapping = $upload->getColumnsMapper()->getDefaultMapping($headers);
        $options['header_mapping'] = $mapping;

        $upload->setAttributeValue('config_read', $options);

        $this->entityManager->persist($upload);
        $this->entityManager->flush();
    }

    private function paginateIfApply(QueryBuilder $query, Request $request): iterable
    {
        if ($this->paginator) {
            $items = $this->paginator->paginate(
                $query,
                $request->query->get('page', 1),
                $request->query->get('per_page', 10)
            );
        } else {
            $items = $query->getQuery()->getResult();
        }

        return $items;
    }
}