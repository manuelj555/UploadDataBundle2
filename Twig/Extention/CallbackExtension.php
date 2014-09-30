<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Twig\Extention;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CallbackExtension extends \Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'upload_data.callback_extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('upload_upload_callback', function (\Closure $callable, $argumnets = array()) {
                return call_user_func_array($callable, $argumnets);
            })
        );
    }


}