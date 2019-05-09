<?php
/**
 * @package plugins.segmenter
 */
class SegmenterPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'segmenter';
	
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
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::SEGMENTER)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			$params = $constructorArgs['params'];
			return new VOperationEngineSegmenter($params->segmenterCmd, $constructorArgs['outFilePath']);
		}
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(SegmenterConversionEngineType::SEGMENTER))
		{
			return new VDLOperatorSegmenter($enumValue);
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
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(SegmenterConversionEngineType::SEGMENTER))
			return 'VOperationEngineSegmenter';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(SegmenterConversionEngineType::SEGMENTER))
			return 'VDLOperatorSegmenter';
		
		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('SegmenterConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('SegmenterConversionEngineType');
			
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
