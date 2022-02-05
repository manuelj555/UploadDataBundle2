<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Twig\Extention;

use Manuel\Bundle\UploadDataBundle\Twig\Extention\Runtime\UploadRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('upload_transferred', [UploadRuntime::class, 'isTransferred']),
            new TwigTest('upload_validated', [UploadRuntime::class, 'isValidated']),
            new TwigTest('upload_read', [UploadRuntime::class, 'isRead']),
            new TwigTest('upload_action_actionable', [UploadRuntime::class, 'isActionActionable']),
            new TwigTest('upload_action_completed', [UploadRuntime::class, 'isActionCompleted']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('upload_columns', [UploadRuntime::class, 'getColumns']),
            new TwigFunction('upload_column_titles', [UploadRuntime::class, 'getColumnTitles']),
            new TwigFunction('upload_items', [UploadRuntime::class, 'getRows']),
            new TwigFunction('upload_item_value', [UploadRuntime::class, 'getItemValue']),
        ];
    }
}