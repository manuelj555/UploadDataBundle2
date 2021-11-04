<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Data\MatchInfo;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use function dd;

/**
 * Class ExcelHeadersMatcher
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class ExcelHeadersMatcher
{
    /**
     * @var ExcelReader
     */
    private $excelReader;

    /**
     * ExcelHeadersMatcher constructor.
     *
     * @param ExcelReader $excelReader
     */
    public function __construct(ExcelReader $excelReader)
    {
        $this->excelReader = $excelReader;
    }

    /**
     * @param UploadConfig $config
     * @param Upload $upload
     * @param array $options
     * @return MatchInfo
     */
    public function getDefaultMatchInfo(UploadConfig $config, Upload $upload, array $options = []): MatchInfo
    {
        $options = array_filter([
                'row_headers' => $upload->getAttributeValue('row_headers') ?: 1,
            ]) + $options;

        $headers = $this->excelReader->getRowHeaders($upload->getFullFilename(), $options);
        $columnsMapper = $config->getColumnsMapper();
        $columns = $columnsMapper->getColumns();
        $matches = $columnsMapper->match($headers);

        return new MatchInfo($upload, $headers, $columns, $matches, $options);
    }

    /**
     * @param UploadConfig $config
     * @param MatchInfo $info
     * @param array $matchData
     * @return array
     */
    public function applyMatch(UploadConfig $config, MatchInfo $info, array $matchData): array
    {
        $columnsMapper = $config->getColumnsMapper();
        $upload = $info->getUpload();
        $mappedData = $columnsMapper->mapForm($matchData, $info->getFileHeaders());

        $options = $info->getOptions();
        $options['header_mapping'] = $mappedData;

        $upload->setAttributeValue('config_read', $options);

        return $mappedData;
    }
}