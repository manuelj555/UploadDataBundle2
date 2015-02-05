<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CsvReader extends BaseReader
{

    public function getData($filename, $options)
    {
        $this->verifyFile($filename);

        $options = $this->resolveOptions($options);

        $file = new \SplFileObject($filename, 'rb');

        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($options['delimiter']);

        $data = array_filter(iterator_to_array($file));

        list($names, $headers) = $options['header_mapping'];
        $formattedData = array();

        $fileHeaders = $formattedData[$options['row_headers']];

        foreach ($data as $rowIndex => $row) {
            $formattedRow = array();
            foreach ($row as $index => $column) {
                if (isset($names[$index])) {
                    $formattedRow[$names[$index]] = $column;
                } else {
                    $formattedRow[self::EXTRA_FIELDS_NAME][$fileHeaders[$index]] = $column;
                }
            }
            $formattedData[$rowIndex] = $formattedRow;
        }

        unset($formattedData[$options['row_headers']], $fileHeaders);

        return $formattedData;
    }

    public
    function supports($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'csv';
    }

    public
    function setDefaultOptions(OptionsResolverInterface $resolver, $headers = false)
    {
        parent::setDefaultOptions($resolver, $headers);

        $resolver->setDefaults(array(
            'delimiter' => '|',
        ));

        $resolver->setRequired(array('row_headers'));
    }

    public
    function getRowHeaders($filename, $options)
    {
        $this->verifyFile($filename);

        $options = $this->resolveOptions($options, true);

        $file = new \SplFileObject($filename, 'rb');

        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($options['delimiter']);

        $file->seek($options['row_headers']);

        $data = $file->current();

        return $data;
    }
}