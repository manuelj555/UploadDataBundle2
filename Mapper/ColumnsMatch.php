<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Manuel\Bundle\UploadDataBundle\Mapper\Exception\DefaultMappingException;
use function array_intersect;
use function array_intersect_key;
use function dd;

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
     * y los values, los nombres  en el archivo de datos.
     *
     * <code>
     *  return ['first_name' => 'First Name'];
     * </code>
     *
     * @param ConfigColumns $columns
     * @param array $fileHeaders
     * @return array
     */
    public static function match(ConfigColumns $columns, array $fileHeaders = array()): array
    {
        $matches = [];

        $originals = $fileHeaders;

        array_walk($fileHeaders, function (&$header) {
            $header = strtolower($header);
        });

        foreach ($columns->getColumns() as $name => $options) {
            $lbl = $columns->getLabel($name);
            if (in_array($lbl, $fileHeaders)) {
                $pos = array_search($lbl, $fileHeaders);
                $matches[$name] = $originals[$pos];
                continue;
            }

            foreach ($fileHeaders as $pos => $header) {
                if (in_array($header, $options['aliases'])) {
                    $matches[$name] = $originals[$pos];
                    unset($fileHeaders[$pos], $originals[$pos]);
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
                            $matches[$name] = $originals[$pos];
                            continue 2;
                        }
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Retorna una matriz con dos posiciones:
     *
     * El primer arreglo es una relacion entre nombres de columna en el archivo y
     * nombre de columna como se ha definido en el archivo de configuración.
     *
     * El segundo es un arreglo que indica la relacion entre los nombres de columna
     * en el archivo y el valor del header leido del archivo.
     *
     * Ejemplo:
     *
     * <code>
     *     [
     *         [
     *             'A' => 'first_name',
     *             'B' => 'last_name',
     *             'C' => 'email',
     *         ],
     *         [
     *             'A' => 'First Name',
     *             'B' => 'Last Name',
     *             'C' => 'Correo Electrónico',
     *         ],
     *     ]
     * </code>
     *
     * @return array
     */
    public static function mapForm(array $data, array $fileHeaders): array
    {
        $matchedData = array_flip($data);
        $validFileHeader = array_intersect_key($fileHeaders, $matchedData);

        return [$matchedData, $validFileHeader];
    }

    /**
     * Retorna el mapping por defecto en base a las columnas definidas y a los headers del archivo.
     *
     * Esta funcion es muy útil cuando se tiene la certesa de que el archivo subido comple con las
     * columnas esperadas tanto en cantidad como en nombre.
     *
     * Es usada por ejemplo cuando la lectura del excel no la hace el usuario sino que va a ser automática.
     *
     * @param array $fileHeaders
     * @return array Ejemplo:
     *
     * <code>
     *     [
     *         [
     *             'A' => 'first_name',
     *             'B' => 'last_name',
     *             'C' => 'email',
     *         ],
     *         [
     *             'A' => 'First Name',
     *             'B' => 'Last Name',
     *             'C' => 'Correo Electrónico',
     *         ],
     *     ]
     * </code>
     *
     * @throws DefaultMappingException si no se pudo hacer un match completo de las columnas se lanza
     * esta exception.
     */
    public static function getDefaultMapping(ConfigColumns $columns, array $fileHeaders): array
    {
        $matches = self::match($columns, $fileHeaders);

        if (count($matches) !== count($columns)) {
            throw new DefaultMappingException(sprintf('Default Mapping requires equals elements count for file headers (%d) and matches result (%d)',
                count($fileHeaders), count($columns)));
        }

        $matchedInFile = array_intersect($matches, $fileHeaders);
        $filteredHeaders = array_intersect($fileHeaders, $matchedInFile);

        // Verificamos que los dos arreglos esten ordenados de igual forma.
        asort($matchedInFile);
        asort($filteredHeaders);

        $values = array_keys($matchedInFile);
        $keys = array_keys($filteredHeaders);
        // combinamos los arreglos, del archivo sacamos las keys y del match los values.
        $expectedColumns = array_combine($keys, $values);

        // Ordenamos por las claves para que concuerde con los headers en el archivo.
        ksort($expectedColumns);

        return [$expectedColumns, $fileHeaders];
    }
}
