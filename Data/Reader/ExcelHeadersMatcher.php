<?php
/**
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Config\ResolvedUploadConfig;
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
    ): ColumnsMatchInfo {
        $headers = $this->excelReader->getHeaders($upload);
        $matches = ColumnsMatch::defaultMatch($resolvedConfig->getConfigColumns(), $headers);

        return new ColumnsMatchInfo(
            $upload,
            $resolvedConfig->getConfigColumns(),
            $headers,
            $matches,
        );
    }

    public function applyMatch(ColumnsMatchInfo $info, array $matchData,): ColumnsMatchInfo
    {
        $matchInfo = new ColumnsMatchInfo(
            $info->getUpload(),
            $info->getConfigColumns(),
            $info->getFileHeaders(),
            $matchData,
        );

        $matchInfo->validate();

        return $matchInfo;
    }
}