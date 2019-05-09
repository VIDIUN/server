<?php
/**
 * @package UI-infra
 * @subpackage bootstrap
 */
require_once __DIR__ . '/../../infra/vEnvironment.php';

/**
 * @package UI-infra
 * @subpackage bootstrap
 */
class Infra_InfraLoader implements Zend_Loader_Autoloader_Interface
{
	public function __construct(Zend_Config $config = null)
	{
		$infaFolder = null;
		$pluginsFolder = null;
		$cachePath = null;
		if($config)
		{
			if(isset($config->cachePath))
				$cachePath = $config->cachePath;
			if(isset($config->infaFolder))
				$infaFolder = $config->infaFolder;
			if(isset($config->pluginsFolder))
				$pluginsFolder = $config->pluginsFolder;
		}
		
		if(!$infaFolder)
			$infaFolder = realpath(dirname(__FILE__) . '/../../infra/');
		if(!$pluginsFolder)
			$pluginsFolder = realpath(dirname(__FILE__) . '/../../plugins/');
		if(!$cachePath)
			$cachePath = vEnvironment::get("cache_root_path") . '/infra/classMap.cache';
		
		require_once($infaFolder . DIRECTORY_SEPARATOR . 'VAutoloader.php');
		require_once($infaFolder . DIRECTORY_SEPARATOR . 'vEnvironment.php');
		
			
		VAutoloader::setClassPath(array($infaFolder . DIRECTORY_SEPARATOR . '*'));
		VAutoloader::addClassPath(VAutoloader::buildPath($pluginsFolder, '*'));
		VAutoloader::setClassMapFilePath($cachePath);
		VAutoloader::register();
	}
	
	public function autoload($class)
	{
		VAutoloader::autoload($class);
	}
}
