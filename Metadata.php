<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class Metadata
{
    protected $rowHeaders = 0;
    protected $separator;

    /**
     * @param mixed $rowHeaders
     */
    public function setRowHeaders($rowHeaders)
    {
        $this->rowHeaders = $rowHeaders;
    }

    /**
     * @return mixed
     */
    public function getRowHeaders()
    {
        return $this->rowHeaders;
    }

    /**
     * @param mixed $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * @return mixed
     */
    public function getSeparator()
    {
        return $this->separator;
    }


}