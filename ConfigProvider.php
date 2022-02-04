<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle;

use Manuel\Bundle\UploadDataBundle\Config\ResolvedUploadConfig;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Mapper\ConfigColumns;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function dd;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ConfigProvider
{
    protected ContainerInterface $container;

    function __construct(
        #[TaggedLocator("upload_data.config")] ContainerInterface $container,
    ) {
        $this->container = $container;
    }

    public function get(string $type, array $options = []): ResolvedUploadConfig
    {
        try {
            $config = $this->container->get($type);
        } catch (NotFoundExceptionInterface) {
            throw new \InvalidArgumentException(sprintf('Tipo "%s" no definido', $type));
        }

        $columns = $this->processConfiguration($config, $options);

        return new ResolvedUploadConfig($config, $columns);
    }

    private function processConfiguration(UploadConfig $config, array $options): ConfigColumns
    {
        $optionsResolver = new OptionsResolver();
        $config->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        return $config->configureColumns($options);
    }
}