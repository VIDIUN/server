<?php
/**
 * @package plugins.smoothProtect
 */
class SmoothProtectPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator, IVidiunPending, IVidiunBatchJobDataContributor
{
	const PLUGIN_NAME = 'smoothProtect';
	const PARAMS_STUB = '__params__';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$playReadyDependency = new VidiunDependency(PlayReadyPlugin::getPluginName());
		$ismIndexDependency = new VidiunDependency(IsmIndexPlugin::getPluginName());
		
		return array($playReadyDependency, $ismIndexDependency);
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::SMOOTHPROTECT)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			$params = $constructorArgs['params'];
			return new VOperationEngineSmoothProtect($params->smoothProtectCmd, $constructorArgs['outFilePath']);
		}
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(SmoothProtectConversionEngineType::SMOOTHPROTECT))
		{
			return new VDLOperatorSmoothProtect($enumValue);
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
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(SmoothProtectConversionEngineType::SMOOTHPROTECT))
			return 'VOperationEngineSmoothProtect';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(SmoothProtectConversionEngineType::SMOOTHPROTECT))
			return 'VDLOperatorSmoothProtect';
		
		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('SmoothProtectConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('SmoothProtectConversionEngineType');
			
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
	
	public static function contributeToConvertJobData ($jobType, $jobSubType, vConvertJobData $jobData)
	{
		if($jobType == BatchJobType::CONVERT && $jobSubType == self::getApiValue(SmoothProtectConversionEngineType::SMOOTHPROTECT))
			return IsmIndexPlugin::addIsmManifestsToSrcFileSyncDesc($jobData);
		else 
			return $jobData;
	}
}
