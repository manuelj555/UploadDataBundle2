<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Exception;

use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;
use function array_diff_key;
use function array_filter;
use function Symfony\Component\Translation\t;

/**
 * @author Manuel Aguirre
 */
class EmptyMatchForRequiredColumnsException extends InvalidColumnsMatchException
{
    private array $invalidColumns = [];

    public function __construct(ConfigColumns $columns, array $matchData)
    {
        $matchedColumns = array_filter($matchData);
        $requiredColumns = array_filter(
            $columns->getColumns(),
            fn($config) => $config['required']
        );
        $this->invalidColumns = array_map(
            fn($config) => $config['label'],
            array_diff_key($requiredColumns, $matchedColumns)
        );

        parent::__construct(t('upload.required_columns.empty', [
            'columns' => $this->invalidColumns,
        ]));
    }
}