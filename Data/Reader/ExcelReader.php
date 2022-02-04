<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use LogicException;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use function array_search;
use function current;
use function dd;
use function in_array;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ExcelReader extends BaseReader
{
    private array $extensions = ['xls', 'xlsx'];
    private ?array $excelHeaders;
    private ?array $columnsMapping;

    public function getData(Upload $upload): array
    {
        $filename = $this->resolveFile($upload->getFullFilename());
        $options = $this->resolveOptions($upload);
        $excel = $this->load($filename);

        $sheet = $excel->getActiveSheet();

        $rowHeadersIndex = $options['row_headers'];
        $lastColumn = $sheet->getHighestColumn($rowHeadersIndex);

        $excelHeaders = $sheet->rangeToArray('A' . $rowHeadersIndex
            . ':' . $lastColumn . $rowHeadersIndex, null, true, true, true);

        $sheet->garbageCollect();
        $maxRow = $sheet->getHighestRow();
        $rows = range($rowHeadersIndex + 1, $maxRow);
        $cols = range(1, Coordinate::columnIndexFromString($lastColumn));

        $this->excelHeaders = current($excelHeaders);
        $this->columnsMapping = $options['columns_mapping'];
        $formattedData = [];

        foreach ($rows as $rowIndex) {
            $formattedRow = [];

            foreach ($cols as $colIndex) {
                [$rawValue, $value] = $this->getValuesFromCell($sheet, $colIndex, $rowIndex);

                $this->addValue($formattedRow, $colIndex, $rawValue, $value);
            }

            $formattedData[$rowIndex] = $formattedRow;
        }

        $this->excelHeaders = null;
        $this->columnsMapping = null;

        $excel->disconnectWorksheets();
        unset($excel, $sheet);

        return $formattedData;
    }

    public function getHeaders(Upload $upload): array
    {
        $filename = $this->resolveFile($upload->getFullFilename());
        $options = $this->resolveOptions($upload, true);
        $excel = $this->load($filename);

        $iterator = $excel->getActiveSheet()
            ->getRowIterator($options['row_headers'])
            ->current()
            ->getCellIterator();

//        $iterator->setIterateOnlyExistingCells(false);

        $headers = array();
        foreach ($iterator as $index => $column) {
            $headers[$column->getColumn()] = $column->getValue();
        }

        $excel->disconnectWorksheets();
        unset($excel);

        return $headers;
    }

    public function supports(Upload $upload): bool
    {
        return $this->matchExtensions($upload, $this->extensions);
    }

    public function configureOptions(OptionsResolver $resolver, bool $headers = false): void
    {
        parent::configureOptions($resolver, $headers);

        $resolver->setRequired([
            'row_headers',
        ]);
    }

    public function loadExcelFromUpload(Upload $upload): Spreadsheet
    {
        $filename = $this->resolveFile($upload->getFullFilename());

        return $this->load($filename);
    }

    protected function load($filename): Spreadsheet
    {
        return IOFactory::load($filename);
    }

    private function getValuesFromCell(
        Worksheet $sheet,
        int $colIndex,
        int $rowIndex,
    ): array {
        $cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex, false);

        if (null !== $cell) {
            if ($cell->isFormula()) {
                $rawValue = $cell->getCalculatedValue();
            } else {
                $rawValue = $cell->getValue();
            }
            if ($rawValue !== null) {
                $value = NumberFormat::toFormattedString(
                    $rawValue, $cell->getStyle()->getNumberFormat()->getFormatCode()
                );
            } else {
                $value = null;
            }
        } else {
            $rawValue = $value = null;
        }

        return [$rawValue, $value];
    }

    private function addValue(
        array &$row,
        int $colIndex,
        ?string $rawValue,
        ?string $formattedValue
    ): void {
        if (null === $this->excelHeaders || null === $this->columnsMapping) {
            throw new LogicException(
                "No se puede llamar a 'getArrayValue()' sin establecer valores para 'excelHeaders' y 'columnsMapping'"
            );
        }

        $excelColName = Coordinate::stringFromColumnIndex($colIndex);

        if (in_array($excelColName, $this->columnsMapping)) {
            $configColumnName = array_search($excelColName, $this->columnsMapping);

            $row[$configColumnName] = [
                'with_format' => $formattedValue,
                'without_format' => $rawValue,
            ];
        } elseif (isset($this->excelHeaders[$excelColName])) {
            $row[self::EXTRA_FIELDS_NAME][$this->excelHeaders[$excelColName]] = [
                'with_format' => $formattedValue,
                'without_format' => $rawValue,
            ];
        }
    }
}