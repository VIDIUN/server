<?php
/**
 * @package server-infra
 * @subpackage config
 */
setlocale(LC_ALL, 'en_US.UTF-8');
libxml_disable_entity_loader(true);

// to make sure stream calls to localhost machine
stream_wrapper_unregister ('http');
stream_wrapper_unregister ('https');

$include_path = realpath(__DIR__ . '/../../vendor/ZendFramework/library') . PATH_SEPARATOR . get_include_path();
set_include_path($include_path);

require_once __DIR__ . '/../../infra/vEnvironment.php';
require_once __DIR__ . '/vConfCacheManager.php';

/**
 * Manages all Vidiun configurations
 * @package server-infra
 * @subpackage Configuration
 */
class vConf extends vEnvironment
{
	public static function hasMap($mapName)
	{
		return vConfCacheManager::hasMap($mapName);
	}

	public static function getMap($mapName)
	{
		return vConfCacheManager::load($mapName);
	}

	protected static function getInternal($paramName, $mapName)
	{
		$map = self::getMap($mapName);
		if(isset($map[$paramName]))
			return $map[$paramName];
		return null;
	}

	public static function hasParam($paramName, $mapName = 'local')
	{
		$result = self::getInternal($paramName, $mapName);
		return !is_null($result);
	}

	public static function get($paramName, $mapName = 'local', $defaultValue = false)
	{
		$result = self::getInternal($paramName, $mapName);
		if (is_null($result))
		{
			if ($defaultValue === false)
				throw new Exception("Cannot find [$paramName] in config");
			return $defaultValue;
		}
		return $result;
	}

	/**
	 * Adds the ability to get an element from array(Section) of configuration directly instead of the Entire array
	 * @param string $sectionName
	 * @param string $paramName
	 * @param string $mapName
	 * @param mixed $defaultValue
	 * @return bool|mixed
	 * @throws Exception
	 */
	public static function getArrayValue($paramName, $sectionName, $mapName = 'local', $defaultValue = false)
	{
		$result = vConf::get($sectionName, $mapName, $defaultValue);
		if (is_array($result) && isset($result[$paramName]))
			return $result[$paramName];
		return $defaultValue;
	}

	public static function getCachedVersionId()
	{
		return vConfCacheManager::loadKey();
	}

	public static function getAll()
	{
		return self::getMap('local');
	}
	
	public static function getDB()
	{
		return self::getMap('db');
	}
}

