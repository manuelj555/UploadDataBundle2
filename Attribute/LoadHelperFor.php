<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Attribute;

use Attribute;
use Exception;
use InvalidArgumentException;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use function dd;
use function is_a;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class LoadHelperFor
{
    public function __construct(
        private string $configClass,
        private array $options = [],
    ) {
        if (!is_a($this->configClass, UploadConfig::class, true)) {
            throw new InvalidArgumentException(
                "El argumento \$configClass debe ser una subclase de "
                . UploadConfig::class . " pero llegó " . $this->configClass
            );
        }
    }

    public function getConfigClass(): string
    {
        return $this->configClass;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}