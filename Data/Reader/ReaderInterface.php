<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
interface ReaderInterface
{
	const EXTRA_FIELDS_NAME = '__EXTRA__';
	
	public function getData($filename, $options);
	
	public function getRowHeaders($filename, $options);
	
	public function supports($filename);
	
	public function setDefaultOptions(OptionsResolver $resolver, $headers = false);
	
	public function setRouteConfig($route);
	
	public function getRouteConfig();
}