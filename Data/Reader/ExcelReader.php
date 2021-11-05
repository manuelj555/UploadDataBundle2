<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ExcelReader extends BaseReader
{

    protected $extensions = array('xls', 'xlsx');

    public function getData($filename, $options)
    {
        $filename = $this->resolveFile($filename);
        $excel = $this->load($filename);

        $options = $this->resolveOptions($options);

        $sheet = $excel->getActiveSheet();

        $lastColumn = $sheet->getHighestColumn($options['row_headers']);

        $excelHeaders = $sheet->rangeToArray('A' . $options['row_headers']
            . ':' . $lastColumn . $options['row_headers'], null, true, true, true);
        $excelHeaders = current($excelHeaders);

        $sheet->garbageCollect();
        $maxRow = $sheet->getHighestRow();
        $rows = range($options['row_headers'] + 1, $maxRow);
        $cols = range(0, Coordinate::columnIndexFromString($lastColumn));

        list($names, $headers) = $options['header_mapping'];
        $formattedData = array();

        foreach ($rows as $rowIndex) {
            $formattedRow = array();

            foreach ($cols as $colIndex) {
                $cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex, false);
                $colName = Coordinate::stringFromColumnIndex($colIndex);

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

                if (isset($names[$colName])) {
                    $formattedRow[$names[$colName]]['with_format'] = $value;
                    $formattedRow[$names[$colName]]['without_format'] = $rawValue;
                } elseif (isset($excelHeaders[$colName])) {
                    $formattedRow[self::EXTRA_FIELDS_NAME][$excelHeaders[$colName]] = $value;
                }
            }

            $formattedData[$rowIndex] = $formattedRow;
        }

        $excel->disconnectWorksheets();
        unset($excel, $sheet);

        return $formattedData;
    }

    public function getRowHeaders($filename, $options)
    {
        $filename = $this->resolveFile($filename);
        $excel = $this->load($filename);

        $options = $this->resolveOptions($options, true);

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

    public function supports($filename)
    {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $this->extensions);
    }

    public function setDefaultOptions(OptionsResolver $resolver, $headers = false)
    {
        parent::setDefaultOptions($resolver, $headers);

        $resolver->setRequired(array(
            'row_headers',
        ));
    }

    public function loadExcelFromUpload(Upload $upload):Spreadsheet
    {
        $filename = $this->resolveFile($upload->getFullFilename());

        return $this->load($filename);
    }

    protected function load($filename): Spreadsheet
    {
        return IOFactory::load($filename);
    }

}