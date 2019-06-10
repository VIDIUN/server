<?php

/**
 * @package server-infra
 * @subpackage cache
 */
class vLoggerCache
{
	const LOGGER_APC_CACHE_KEY_PREFIX = 'LoggerInstance_';

	/**
	 * @param $configName string
	 * @param $context string
	 */
	static public function InitLogger($configName, $context = null)
	{
		if (VidiunLog::getLogger())	// already initialized
			return;
		
		if (function_exists('apc_fetch'))
		{
			$cacheKey = self::LOGGER_APC_CACHE_KEY_PREFIX . $configName;
			$logger = apc_fetch($cacheKey);
			if ($logger)
			{
				list($logger, $cacheVersionId) = $logger;
				if ($cacheVersionId == vConf::getCachedVersionId())
				{
					VidiunLog::setLogger($logger);
					return;
				}
			}
		}

		try // we don't want to fail when logger is not configured right
		{
			$config = new Zend_Config(vConf::getMap('logger'));
			
			VidiunLog::initLog($config->$configName);
			if ($context)
				VidiunLog::setContext($context);
					
			if (function_exists('apc_store'))
				apc_store($cacheKey, array(VidiunLog::getLogger(), vConf::getCachedVersionId()));
		}
		catch(Zend_Config_Exception $ex)
		{
		}
	}
}
