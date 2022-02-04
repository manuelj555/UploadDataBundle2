<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Config\ResolvedUploadConfig;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Data\ColumnsMatchInfo;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMatch;

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

    public function getDefaultMatchInfo(
        ResolvedUploadConfig $resolvedConfig,
        Upload $upload,
        array $options = [],
    ): ColumnsMatchInfo {
        $headers = $this->excelReader->getHeaders($upload);
        $matches = ColumnsMatch::match($resolvedConfig->getConfigColumns(), $headers);

        return new ColumnsMatchInfo(
            $upload,
            $headers,
            $resolvedConfig->getConfigColumns()->getColumns(),
            $matches,
            $options
        );
    }

    public function applyMatch(ResolvedUploadConfig $resolvedConfig, ColumnsMatchInfo $info, array $matchData): array
    {
        $columnsMapper = $resolvedConfig->getConfigColumns();
        $upload = $info->getUpload();
        $mappedData = $columnsMapper->mapForm($matchData, $info->getFileHeaders());

        $options = $info->getOptions();
        $options['header_mapping'] = $mappedData;

        $upload->setAttributeValue('config_read', $options);

        return $mappedData;
    }
}