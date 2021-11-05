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
class MatchInfo
{
    /**
     * @var Upload
     */
    private $upload;
    /**
     * @var array
     */
    private $fileHeaders;
    /**
     * @var array
     */
    private $configuredColumns;
    /**
     * @var array
     */
    private $matchedColumns;
    /**
     * @var array
     */
    private $options;

    /**
     * MatchInfo constructor.
     *
     * @param Upload $upload
     * @param $fileHeaders
     * @param $configuredColumns
     * @param $matchedColumns
     * @param array $options
     */
    public function __construct(Upload $upload, $fileHeaders, $configuredColumns, $matchedColumns, array $options)
    {
        $this->upload = $upload;
        $this->fileHeaders = $fileHeaders;
        $this->configuredColumns = $configuredColumns;
        $this->matchedColumns = $matchedColumns;
        $this->options = $options;
    }

    public function getUpload():Upload
    {
        return $this->upload;
    }

    public function getFileHeaders():array
    {
        return $this->fileHeaders;
    }

    public function getConfiguredColumns():?array
    {
        return $this->configuredColumns;
    }

    public function getMatchedColumns():?array
    {
        return $this->matchedColumns;
    }

    public function getOptions():array
    {
        return $this->options;
    }

    public function matched(?string $columnName, ?string $fileHeaderName):bool
    {
        return isset($this->matchedColumns[$columnName]) && $fileHeaderName == $this->matchedColumns[$columnName];
    }
}