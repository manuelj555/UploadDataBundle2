<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Data\UploadedFileHelperInterface;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;
use function array_keys;
use function in_array;
use function pathinfo;
use function strtolower;
use const PATHINFO_EXTENSION;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class BaseReader implements ReaderInterface
{
    protected UploadedFileHelperInterface $uploadedFileHelper;

    #[Required]
    public function setUploadedFileHelper(UploadedFileHelperInterface $uploadedFileHelper)
    {
        $this->uploadedFileHelper = $uploadedFileHelper;
    }

    public function configureOptions(OptionsResolver $resolver, bool $headers = false): void
    {
        if (!$headers) {
            $resolver->setRequired(['columns_mapping']);
        }
    }

    protected function resolveOptions(Upload $upload, $headers = false)
    {
        $resolver = new OptionsResolver();
        $options = $upload->getReadOptions();
        $resolver->setDefined(array_keys($options));

        $this->configureOptions($resolver, $headers);

        return $resolver->resolve($options);
    }

    protected function resolveFile($filename)
    {
        $filename = $this->uploadedFileHelper->prepareFileForRead($filename);

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $filename));
        }

        return $filename;
    }

    protected function matchExtensions(Upload $upload, array $extensions): bool
    {
        return in_array(strtolower(pathinfo($upload->getFullFilename(), PATHINFO_EXTENSION)), $extensions);
    }
}