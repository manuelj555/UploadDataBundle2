<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ColumnsMapper
{
    protected $columns = array();
    protected $labels = array();
    protected $matches = array();

    public function add($name, array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'aliases' => array(),
            'label' => $name,
            'name' => $name,
            'required' => true,
            'similar' => function (Options $options){
                return count($options['aliases']) > 0;
            },
            'formatter' => function ($value) { return $value; },
        ));
        $resolver->setNormalizers(array(
            'aliases' => function (Options $options, $value) {
                $value[] = $options['label'];
                $value[] = $options['name'];

                return array_map(function ($alias) { return strtolower($alias); }, $value);
            },
        ));

        $options = $resolver->resolve($options);

        $this->columns[$name] = $options;

        $this->labels[$name] = $options['label'];

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
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
        return isset($this->labels[$name]) ? $this->labels[$name] : null;
    }

    public function match($fileHeaders = array())
    {
        $this->matches = array();

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

    public function mapForm(array $data, array $fileHeaders)
    {
//        $data = array_flip($data);
//        ksort($data);

        return array(array_flip($data), $fileHeaders);

//        return array_combine($data, $fileHeaders);
    }
}