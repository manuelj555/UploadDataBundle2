<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnList;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class TextColumn extends AbstractColumn
{

    public function getType()
    {
        return 'text';
    }
}