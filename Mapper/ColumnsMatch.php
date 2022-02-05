<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
final class ColumnsMatch
{
    /**
     * Retorna un arreglo con las columnas del archivo de datos que se pudieron mapear
     * de forma automatica con las columnas definidas en el archivo
     * de configuración.
     *
     * Las clases son las columnas definidas en el archivo de configuración
     * y los values, los nombres de las columnas (A, B, C) en el archivo de datos.
     *
     * <code>
     *  return ['first_name' => 'B'];
     * </code>
     *
     * @param ConfigColumns $columns
     * @param array $fileHeaders
     * @return array
     */
    public static function defaultMatch(ConfigColumns $columns, array $fileHeaders): array
    {
        $matches = [];

        array_walk($fileHeaders, function (&$header) {
            $header = strtolower($header);
        });

        foreach ($columns->getColumns() as $name => $options) {
            foreach ($fileHeaders as $pos => $header) {
                if (in_array($header, $options['aliases'])) {
                    $matches[$name] = $pos;
                    unset($fileHeaders[$pos]);
                    continue 2;
                }
            }
            if ($options['similar']) {
                //buscamos parecidos
                foreach ($fileHeaders as $pos => $header) {
                    $word = null;
                    $min = 100;
                    foreach ($options['aliases'] as $alias) {
                        $lev = levenshtein($header, $alias);
                        if ($lev <= strlen($alias) / 3) {
                            $matches[$name] = $pos;
                            continue 2;
                        }
                    }
                }
            }
        }

        return $matches;
    }
}
