<?php
/**
 * @package plugins.webexNbrplayer
 */
class WebexNbrplayerPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'webexNbrplayer';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::WEBEX_NBRPLAYER)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			$params = $constructorArgs['params'];
			return new VOperationEngineWebexNbrplayer($params->webexNbrplayerCmd, $constructorArgs['outFilePath']);
		}
			
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(WebexNbrplayerConversionEngineType::WEBEX_NBRPLAYER))
		{
			return new VDLOperatorWebexNbrplayer($enumValue);
		}
		
		return null;
	}

	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(WebexNbrplayerConversionEngineType::WEBEX_NBRPLAYER))
			return 'VOperationWebexNbrplayer';
			
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(WebexNbrplayerConversionEngineType::WEBEX_NBRPLAYER))
			return 'VDLOperatorWebexNbrplayer';
		
		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('WebexNbrplayerConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('WebexNbrplayerConversionEngineType');
			
		return array();
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getConversionEngineCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('conversionEngineType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}
