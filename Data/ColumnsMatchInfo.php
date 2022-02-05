<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;

/**
 * Class MatchInfo
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class ColumnsMatchInfo
{
    public function __construct(
        private Upload $upload,
        private ConfigColumns $configColumns,
        private array $fileHeaders,
        private array $matchedColumns,
    ) {
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
}