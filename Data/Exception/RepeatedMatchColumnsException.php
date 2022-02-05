<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Exception;

use function array_filter;
use function array_keys;
use function array_map;
use function dd;
use function Symfony\Component\Translation\t;

/**
 * @author Manuel Aguirre
 */
class RepeatedMatchColumnsException extends InvalidColumnsMatchException
{
    private array $repeatedColumns = [];

    public function __construct(array $fileHeaders, array $matchData)
    {
        $grouped = [];

        foreach ($matchData as $column => $excelCol) {
            $grouped[$excelCol][] = $column;
        }

        $repeated = array_keys(array_filter($grouped, fn($group) => 1 < count($group)));
        $this->repeatedColumns = array_map(fn($col) => $fileHeaders[$col] ?? null, $repeated);

        parent::__construct(t('upload.upload_columns.repeated_items', [
            'columns' => $this->repeatedColumns,
        ]));
    }
}