<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;

/**
 * @author Manuel Aguirre
 */
class ResolvedUploadConfig
{
    public function __construct(
        private UploadConfig $config,
        private ConfigColumns $columns,
    ) {
    }

    public function getConfig(): UploadConfig
    {
        return $this->config;
    }

    public function getConfigColumns(): ConfigColumns
    {
        return $this->columns;
    }
}