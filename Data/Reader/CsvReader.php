<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CsvReader extends BaseReader
{
    public function getData(Upload $upload): array
    {
        $filename = $this->resolveFile($upload->getFullFilename());
        $options = $this->resolveOptions($upload);

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

    public function supports(Upload $upload): bool
    {
        return $this->matchExtensions($upload, ['csv']);
    }

    public function configureOptions(OptionsResolver $resolver, bool $headers = false): void
    {
        parent::configureOptions($resolver, $headers);

        $resolver->setDefaults(array(
            'delimiter' => '|',
        ));

        $resolver->setRequired('row_headers');
    }

    public function getHeaders(Upload $upload): array
    {
        $filename = $this->resolveFile($upload->getFullFilename());
        $options = $this->resolveOptions($upload, true);
        $file = new \SplFileObject($filename, 'rb');

        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($options['delimiter']);

        $file->seek($options['row_headers']);

        return $file->current();
    }
}