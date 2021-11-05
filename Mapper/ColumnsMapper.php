<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Manuel\Bundle\UploadDataBundle\Mapper\Exception\DefaultMappingException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_intersect;
use function array_intersect_key;
use function dd;
use function dump;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnsMapper
{
    protected $columns = [];
    protected $labels = [];
    protected $matches = [];

    public function add($name, array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'aliases' => [],
            'label' => $name,
            'name' => $name,
            'required' => true,
            'similar' => function (Options $options){
                return count($options['aliases']) > 0;
            },
            'formatter' => function ($value) { return $value; },
        ]);
        $resolver->setNormalizer('aliases', function (Options $options, $value) {
                $value[] = $options['label'];
                $value[] = $options['name'];

                return array_map(function ($alias) { return strtolower($alias); }, $value);
            }
        );

        $options = $resolver->resolve($options);

        $this->columns[$name] = $options;

        $this->labels[$name] = $options['label'];

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumnsAsArray()
    {
        $columns = [];

        foreach ($this->columns as $key => $config){
            $columns[$key] = $config['label'];
        }

        return $columns;
    }

    public function getNames()
    {
        return array_keys($this->columns);
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function getLabel($name)
    {
        return $this->labels[$name] ?? null;
    }

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
     * @param array $fileHeaders
     * @return array
     */
    public function match($fileHeaders = array())
    {
        $this->matches = [];

        $originals = $fileHeaders;

        array_walk($fileHeaders, function (&$header) {
            $header = strtolower($header);
        });


        foreach ($this->columns as $name => $options) {
            $lbl = strtolower($this->labels[$name]);
            if (in_array($lbl, $fileHeaders)) {
                $pos = array_search($lbl, $fileHeaders);
                $this->matches[$name] = $originals[$pos];
                continue;
            }

            foreach ($fileHeaders as $pos => $header) {
                if (in_array($header, $options['aliases'])) {
                    $this->matches[$name] = $originals[$pos];
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
                            $this->matches[$name] = $originals[$pos];
                            continue 2;
                        }
                    }
                }
            }
        }

        return $this->matches;
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function isMatched($name)
    {
        return isset($this->matches[$name]);
    }

    public function getMatch($name)
    {
        return $this->isMatched($name) ? $this->matches[$name] : null;
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
    public function mapForm(array $data, array $fileHeaders)
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
    public function getDefaultMapping(array $fileHeaders)
    {
        $matches = $this->match($fileHeaders);

        if(count($matches) !== count($this->getColumns())){
            throw new DefaultMappingException(sprintf('Default Mapping requires equals elements count for file headers (%d) and matches result (%d)', count($fileHeaders), count($this->getColumns())));
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
