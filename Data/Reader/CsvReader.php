<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Data\Reader;

use Manuelj555\Bundle\UploadDataBundle\Metadata;
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

        foreach ($data as  $rowIndex => $row) {
            $formattedRow = array();
            foreach ($row as $index => $column) {
                $formattedRow[$names[$index]] = $column;
            }
            $formattedData[$rowIndex] = $formattedRow;
        }

        unset($formattedData[$options['row_headers']]);

        return $formattedData;
    }

    public function supports($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION) === 'csv';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver, $headers = false)
    {
        $resolver->setDefaults(array(
            'delimiter' => '|',
        ));

        $resolver->setRequired(array('row_headers'));

        if (!$headers) {
            $resolver->setRequired(array('header_mapping'));
        }

    }

    public function getRowHeaders($filename, $options)
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