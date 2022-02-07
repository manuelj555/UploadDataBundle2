<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Attribute;

use Attribute;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class LoadHelperForUpload
{
    public function __construct(
        private string $uploadParameterName,
        private array $options = [],
    ) {
    }

    public function getUploadParameterName(): string
    {
        return $this->uploadParameterName;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}