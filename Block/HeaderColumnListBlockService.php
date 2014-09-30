<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Block;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class HeaderColumnListBlockService extends BaseBlockService
{
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $config = $blockContext->getSetting('config');
        /* @var $config UploadConfig */

        $columns = $config->getListMapper()->getColumns();

        return $this->renderPrivateResponse('@UploadData/Block/header_columns.html.twig', array(
            'columns' => $columns,
            'upload_type' => $config->getType(),
            'is_show' => $blockContext->getSetting('is_show'),
        ));
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('is_show' => false));
        $resolver->setRequired(array('config'));
        $resolver->setAllowedTypes(array(
            'config' => 'Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig',
        ));
    }


} 