<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Block;

use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadListBlockService extends BaseBlockService
{
    protected $uploadTypes = array();

    /**
     * @param array $uploadTypes
     */
    public function setUploadTypes($uploadTypes)
    {
        $this->uploadTypes = $uploadTypes;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderPrivateResponse('@UploadData/Upload/upload_types.html.twig', array(
            'types' => $this->uploadTypes,
        ));
    }


} 