<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Manuel\Bundle\UploadDataBundle\Data\MatchInfo;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ExcelHeadersMatcher;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItemRepository;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Manuel Aguirre
 */
class ConfigHelper
{
    /**
     * @var UploadConfig
     */
    private $config;
    /**
     * @var ExcelHeadersMatcher
     */
    private $headersMatcher;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UploadRepository
     */
    private $repository;
    /**
     * @var UploadedItemRepository
     */
    private $itemRepository;
    /**
     * @var PaginatorInterface|null
     */
    private $paginator;
    /**
     * @var LoggerInterface|null
     */
    private $logger;
    /**
     * @var Exception|null
     */
    private $lastException;

    public function __construct(
        UploadConfig $config,
        ExcelHeadersMatcher $headersMatcher,
        EntityManagerInterface $entityManager,
        UploadRepository $repository,
        UploadedItemRepository $itemRepository,
        ?PaginatorInterface $paginator = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->headersMatcher = $headersMatcher;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemRepository = $itemRepository;
        $this->paginator = $paginator;
        $this->logger = $logger;
    }

    public function getConfig(): UploadConfig
    {
        return $this->config;
    }

    public function getListData(Request $request, array $filters = null): array
    {
        $query = $this->config->getQueryList(
            $this->repository, $filters
        );

        return $this->paginateIfApply($query, $request);
    }

    public function upload(UploadedFile $uploadedFile, array $formData = [], array $uploadAttributes = []): Upload
    {
        return $this->getConfig()->processUpload($uploadedFile, $formData, $uploadAttributes);
    }

    public function validate(Upload $upload): bool
    {
        try {
            $this->getConfig()->processValidation($upload);

            return true;
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la lectura del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }

        return false;
    }

    public function transfer(Upload $upload): bool
    {
        try {
            $this->config->processTransfer($upload);

            return true;
        } catch (\Exception $e) {
            $this->lastException = $e;

            if ($this->logger) {
                $this->logger->critical('No se pudo procesar la transferencia del excel', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }

        return false;
    }

    public function customAction(Upload $upload, string $action): bool
    {
        try {
            $this->config->processActionByName($upload, $action);

            return true;
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
        }

        return false;
    }

    public function show(Upload $upload, Request $request): array
    {
        $query = $this->itemRepository->getQueryByUpload($upload, $request->query->all());

        return $this->paginateIfApply($query, $request);
    }

    public function delete(Upload $upload): bool
    {
        try {
            $this->config->processDelete($upload);

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

    public function getDefaultMatchInfo(Upload $upload, array $options = []): MatchInfo
    {
        return $this->headersMatcher->getDefaultMatchInfo($this->getConfig(), $upload, $options);
    }

    public function applyMatch(MatchInfo $matchInfo, array $matchData): array
    {
        $match = $this->headersMatcher->applyMatch($this->getConfig(), $matchInfo, $matchData);

        $this->entityManager->persist($matchInfo->getUpload());
        $this->entityManager->flush();

        return $match;
    }

    public function configureDefaultMatch(Upload $upload, array $options = []):void
    {
        $this->getConfig()->configureDefaultMatch($upload, $options);
    }

    private function paginateIfApply(QueryBuilder $query, Request $request)
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