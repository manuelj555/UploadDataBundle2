<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Data\Reader;

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
        $this->verifyFile($filename);

        $options = $this->resolveOptions($options);

        $sheet = $this->load($filename)->getActiveSheet();

        $lastColumn = $sheet->getHighestColumn($options['row_headers']);

        $excelHeaders = $sheet->rangeToArray('A' . $options['row_headers']
            . ':' . $lastColumn . $options['row_headers'], null, true, true, true);
        $excelHeaders = current($excelHeaders);

        $iterator = $sheet->getRowIterator($options['row_headers'] + 1);

        list($names, $headers) = $options['header_mapping'];
        $formattedData = array();

        foreach ($iterator as $rowCell) {
            /* @var $rowCell \PHPExcel_Worksheet_Row */
            $formattedRow = array();
            foreach ($rowCell->getCellIterator() as $cell) {
                /* @var $cell \PHPExcel_Cell */
                if (isset($names[$cell->getColumn()])) {
                    $formattedRow[$names[$cell->getColumn()]] = $cell->getValue();
                } else {
                    $formattedRow[self::EXTRA_FIELDS_NAME]
                    [$excelHeaders[$cell->getColumn()]]
                        = $cell->getValue();
                }
            }
            if (array_filter($formattedRow)) {
                //solo si hay datos, lo llenamos
                $formattedData[$rowCell->getRowIndex()] = $formattedRow;
            }
        }

        return $formattedData;
    }

    public function getRowHeaders($filename, $options)
    {
        $this->verifyFile($filename);

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

    public function setDefaultOptions(OptionsResolverInterface $resolver, $headers = false)
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