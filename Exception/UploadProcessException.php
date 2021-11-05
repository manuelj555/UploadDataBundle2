<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Exception;

use Exception;
use Throwable;

/**
 * @author Manuel Aguirre
 */
class UploadProcessException extends Exception
{
    /**
     * @var string
     */
    private $type;

    public function __construct(Throwable $throwable, string $type)
    {
        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable);
        $this->type = $type;
    }

    public static function fromMessage(string $message, string $type): self
    {
        return new self(new Exception($message), $type);
    }

    public function getType(): string
    {
        return $this->type;
    }
}