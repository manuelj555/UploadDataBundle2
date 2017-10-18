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

    /**
     * @return Upload
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * @return mixed
     */
    public function getFileHeaders()
    {
        return $this->fileHeaders;
    }

    /**
     * @return mixed
     */
    public function getConfiguredColumns()
    {
        return $this->configuredColumns;
    }

    /**
     * @return mixed
     */
    public function getMatchedColumns()
    {
        return $this->matchedColumns;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $columnName
     * @param $fileHeaderName
     * @return bool
     */
    public function hasMatch($columnName, $fileHeaderName)
    {
        return isset($this->matchedColumns[$columnName]) && $fileHeaderName == $this->matchedColumns[$columnName];
    }
}