<?php

/**
*@package plugins.inletArmada
*/
class InletArmadaPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator
{
	const PLUGIN_NAME = 'inletArmada';
	
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
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::INLET_ARMADA)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			return new VOperationEngineInletArmada("", $constructorArgs['outFilePath']);
		}

		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(InletArmadaConversionEngineType::INLET_ARMADA))
		{
			return new VDLOperatorInletArmada($enumValue);
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
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(InletArmadaConversionEngineType::INLET_ARMADA))
			return 'VOperationEngineInletArmada';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(InletArmadaConversionEngineType::INLET_ARMADA))
			return 'VDLOperatorInletArmada';
		
		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('InletArmadaConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('InletArmadaConversionEngineType');
			
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
