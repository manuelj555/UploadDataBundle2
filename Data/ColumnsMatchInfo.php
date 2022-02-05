<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Manuel\Bundle\UploadDataBundle\Data\Exception\EmptyMatchForRequiredColumnsException;
use Manuel\Bundle\UploadDataBundle\Data\Exception\InvalidColumnsMatchException;
use Manuel\Bundle\UploadDataBundle\Data\Exception\RepeatedMatchColumnsException;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;
use function array_filter;
use function array_unique;
use function count;

/**
 * Class MatchInfo
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class ColumnsMatchInfo
{
    private ?bool $valid = null;
    private ?InvalidColumnsMatchException $validationError = null;

    public function __construct(
        private Upload $upload,
        private ConfigColumns $configColumns,
        private array $fileHeaders,
        private array $matchedColumns,
    ) {
        if ($this->isValid()) {
            $this->upload->setColumnsMatch($this);
        }
    }

    public function getUpload(): Upload
    {
        return $this->upload;
    }

    public function getFileHeaders(): array
    {
        return $this->fileHeaders;
    }

    public function getConfigColumns(): ConfigColumns
    {
        return $this->configColumns;
    }

    public function getMatchedColumns(): ?array
    {
        return $this->matchedColumns;
    }

    public function matched(?string $columnName, ?string $fileHeaderName): bool
    {
        return isset($this->matchedColumns[$columnName]) && $fileHeaderName == $this->matchedColumns[$columnName];
    }

    public function getFileColumnMatch(string $configColumnName): ?string
    {
        return $this->matchedColumns[$configColumnName] ?? null;
    }

    public function isValid(): bool
    {
        if (null !== $this->valid) {
            return $this->valid;
        }

        try {
            $this->validate();

            return $this->valid = true;
        } catch (InvalidColumnsMatchException) {
            return $this->valid = false;
        }
    }

    public function validate(): void
    {
        if (null !== $this->validationError) {
            throw $this->validationError;
        }

        try {
            $this->validateWith($this->getMatchedColumns());
        } catch (InvalidColumnsMatchException $e) {
            $this->validationError = $e;
            throw $e;
        }
    }

    public function validateWith(array $matchData): void
    {
        $matchedColumns = array_filter($matchData);
        $requiredColumnsCount = $this->getConfigColumns()->countRequired();

        if (count($matchedColumns) < $requiredColumnsCount) {
            throw new EmptyMatchForRequiredColumnsException($this->getConfigColumns(), $matchData);
        }

        if ($matchedColumns != array_unique($matchedColumns)) {
            throw new RepeatedMatchColumnsException($this->getFileHeaders(), $matchedColumns);
        }
    }
}