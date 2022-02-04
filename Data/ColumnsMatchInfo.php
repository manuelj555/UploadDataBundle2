<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;

/**
 * Class MatchInfo
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class ColumnsMatchInfo
{
    public function __construct(
        private Upload $upload,
        private array $fileHeaders,
        private array $configuredColumns,
        private array $matchedColumns,
        private array $options
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

    public function getConfiguredColumns(): ?array
    {
        return $this->configuredColumns;
    }

    public function getMatchedColumns(): ?array
    {
        return $this->matchedColumns;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function matched(?string $columnName, ?string $fileHeaderName): bool
    {
        return isset($this->matchedColumns[$columnName]) && $fileHeaderName == $this->matchedColumns[$columnName];
    }
}