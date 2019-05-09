<?php
/**
 * @package plugins.integration
 */
abstract class IntegrationProviderPlugin extends VidiunPlugin implements IIntegrationProviderPlugin, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader
{
	const INTEGRATION_PLUGIN_NAME = 'integration';
	
	//exteding classes should implement this function to make sure 
	//there will be an object implementing IIntegrationProvider interface
	abstract function getProvider();

	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$class = get_called_class();
		$integrationVersion = $class::getRequiredIntegrationPluginVersion();
		$dependency = new VidiunDependency(IntegrationPlugin::getPluginName(), $integrationVersion);
		
		return array($dependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		$class = get_called_class();
		$integrationProviderClassName = $class::getIntegrationProviderClassName();
		if(is_null($baseEnumName))
			return array($integrationProviderClassName);
	
		if($baseEnumName == 'IntegrationProviderType')
			return array($integrationProviderClassName);
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{			
		$class = get_called_class();
		$objectClass = $class::getObjectClass($baseClass, $enumValue);
		if (is_null($objectClass)) 
		{
			return null;
		}
		
		if (!is_null($constructorArgs))
		{
			$reflect = new ReflectionClass($objectClass);
			return $reflect->newInstanceArgs($constructorArgs);
		}
		else
		{
			return new $objectClass();
		}
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getIntegrationProviderCoreValue($valueName)
	{
		$class = get_called_class();
		$value = $class::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('IntegrationProviderType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		$class = get_called_class();
		return $class::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}
