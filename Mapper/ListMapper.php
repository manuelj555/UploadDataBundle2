<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Mapper;

use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\AbstractColumn;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\ColumnFactory;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\LoadedColumn;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ListMapper
{
    protected $columns = array();
    /**
     * @var ColumnFactory
     */
    protected $columnListFactory;
    private $orderedColumns = false;

    /**
     * ListMapper constructor.
     * @param ColumnFactory $columnListFactory
     */
    public function __construct(ColumnFactory $columnListFactory)
    {
        $this->columnListFactory = $columnListFactory;
    }

    public function add($name, $type = null, array $options = array())
    {
        $item = $this->columnListFactory->create($name, $type, $options);

        $this->columns[$name] = $item;
        $this->orderedColumns = false;

        return $this;
    }

    public function addAttribute($name, array $options = array())
    {
        return $this->add($name, 'attribute', $options);
    }

    public function addAction($name, array $options = array())
    {
        return $this->add($name, 'action', $options);
    }

    public function remove($name)
    {
        unset($this->columns[$name]);
        $this->orderedColumns = false;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        if (false === $this->orderedColumns) {
            $this->orderedColumns = true;
            $keys = array_keys($this->columns);

            uasort($this->columns, function (LoadedColumn $a, LoadedColumn $b) use ($keys) {
                $aPosition = $a->getOption('position');
                $bPosition = $b->getOption('position');

                if ($aPosition === $bPosition) {
                    // Si son iguales debemos mantener el orden en que fueron agregados.
                    $initialAPosition = array_search($a->getName(), $keys);
                    $initialBPosition = array_search($b->getName(), $keys);
                    return $initialAPosition < $initialBPosition ? -1 :
                        ($initialAPosition > $initialBPosition ? 1 : 0);
                }

                return $aPosition < $bPosition ? -1 : 1;
            });
        }

        return $this->columns;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param $name
     *
     * @return LoadedColumn
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('The Column List "%s" does not exists', $name));
        }

        return $this->columns[$name];
    }
}