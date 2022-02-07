<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Request\ArgumentResolver;

use InvalidArgumentException;
use LogicException;
use Manuel\Bundle\UploadDataBundle\Attribute\LoadHelper;
use Manuel\Bundle\UploadDataBundle\Attribute\LoadHelperForUpload;
use Manuel\Bundle\UploadDataBundle\Config\ConfigHelper;
use Manuel\Bundle\UploadDataBundle\Config\ConfigHelperFactory;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
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
        $attributes = $argument->getAttributes(LoadHelper::class);

        if (0 === count($attributes)) {
            $attributes = $argument->getAttributes(LoadHelperForUpload::class);

            if (0 === count($attributes)) {
                if (null === ($configClass = $this->tryLoadConfigClassFromRequest($request))) {
                    throw new LogicException(
                        "El parametro 'ConfigHelper \${$argument->getName()}' debe tener el " .
                        "atributo #[LoadHelperFor(ClaseConfigString)] o #[LoadHelperForUpload('upload_param')]"
                    );
                }

                $options = [];
            } else {
                /** @var LoadHelperForUpload $attribute */
                $attribute = $attributes[0];
                $configClass = $this->loadConfigClassFromUpload($attribute->getUploadParameterName(), $request);
                $options = $attribute->getOptions();
            }
        } else {
            /** @var LoadHelper $attribute */
            $attribute = $attributes[0];
            $configClass = $attribute->getConfigClass();
            $options = $attribute->getOptions();
        }

        yield $this->configHelperFactory->createForType($configClass, $options);
    }

    private function loadConfigClassFromUpload(string $uploadParamName, Request $request): string
    {
        if (!$request->attributes->has($uploadParamName)) {
            throw new InvalidArgumentException(
                "No se encontró el parametro '\${$uploadParamName}' en la acción del controlador"
            );
        }

        $upload = $request->attributes->get($uploadParamName);

        if (!$upload instanceof Upload) {
            throw new InvalidArgumentException(
                "El parametro '{$uploadParamName}' definido en el atributo " .
                "#[LoadHelperForUpload('{$uploadParamName}')] no es una instancia de " .
                Upload::class,
            );
        }

        return $upload->getConfigClass();
    }

    private function tryLoadConfigClassFromRequest(Request $request): ?string
    {
        $upload = null;

        foreach ($request->attributes->all() as $attribute) {
            if ($attribute instanceof Upload) {
                if (null !== $upload) {
                    // Si hay más de un upload, no retornamos ningun configClass
                    return null;
                }

                $upload = $attribute;
            }
        }

        return $upload?->getConfigClass();
    }
}