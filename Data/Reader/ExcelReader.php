<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ExcelReader extends BaseReader
{

    protected $extensions = array('xls', 'xlsx');
//    /**
//     * @var \PHPExcel
//     */
//    protected $excel;
//
//    function __construct($excel = null)
//    {
//        $this->excel = $excel ? : new \PHPExcel();
//    }

    public function getData($filename, $options)
    {
        $filename = $this->resolveFile($filename);

        $options = $this->resolveOptions($options);

        $excel = $this->load($filename);
        $sheet = $excel->getActiveSheet();

        $lastColumn = $sheet->getHighestColumn($options['row_headers']);

        $excelHeaders = $sheet->rangeToArray('A'.$options['row_headers']
            .':'.$lastColumn.$options['row_headers'], null, true, true, true);
        $excelHeaders = current($excelHeaders);

        $sheet->garbageCollect();
        $maxRow = $sheet->getHighestRow();
        $rows = range($options['row_headers'] + 1, $maxRow);
        $cols = range(0, \PHPExcel_Cell::columnIndexFromString($lastColumn));

        list($names, $headers) = $options['header_mapping'];
        $formattedData = array();

        foreach ($rows as $rowIndex) {
            $formattedRow = array();

            foreach ($cols as $colIndex) {
                $cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex, false);
                $colName = \PHPExcel_Cell::stringFromColumnIndex($colIndex);

                if (null !== $cell) {
                    if ($cell->isFormula()){
                        $rawValue = $cell->getCalculatedValue();
                    }else{
                        $rawValue = $cell->getValue();
                    }
                    if ($rawValue !== null) {
                        $value = \PHPExcel_Style_NumberFormat::toFormattedString(
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

        $options = $this->resolveOptions($options, true);

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

    /**
     * @param \PHPExcel
     */
    protected function load($filename)
    {
        return \PHPExcel_IOFactory::load($filename);
    }

}