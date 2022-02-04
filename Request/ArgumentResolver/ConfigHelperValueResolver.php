<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Request\ArgumentResolver;

use LogicException;
use Manuel\Bundle\UploadDataBundle\Attribute\LoadHelperFor;
use Manuel\Bundle\UploadDataBundle\Config\ConfigHelper;
use Manuel\Bundle\UploadDataBundle\Config\ConfigHelperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use function count;

/**
 * @author Manuel Aguirre
 */
class ConfigHelperValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private ConfigHelperFactory $configHelperFactory)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ConfigHelper::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributes = $argument->getAttributes(LoadHelperFor::class);

        if (0 === count($attributes)) {
            throw new LogicException(
                "El parametro 'ConfigHelper \${$argument->getName()}' debe tener el " .
                "atributo #[LoadHelperFor(ClaseConfigString)]"
            );
        }

        /** @var LoadHelperFor $attribute */
        $attribute = $attributes[0];

        yield $this->configHelperFactory->createForType(
            $attribute->getConfigClass(),
            $attribute->getOptions(),
        );
    }
}