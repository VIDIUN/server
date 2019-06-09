<?php
/**
 * @package plugins.smilManifest
 */
class SmilManifestPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator, IVidiunBatchJobDataContributor
{
	const PLUGIN_NAME = 'smilManifest';

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
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::SMIL_MANIFEST)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			return new VOperationEngineSmilManifest(null, $constructorArgs['outFilePath']);
		}
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(SmilManifestConversionEngineType::SMIL_MANIFEST))
		{
			return new VDLOperatorSmilManifest($enumValue);
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
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(SmilManifestConversionEngineType::SMIL_MANIFEST))
			return 'VOperationEngineSmilManifest';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(SmilManifestConversionEngineType::SMIL_MANIFEST))
			return 'VDLOperatorSmilManifest';

		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('SmilManifestConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('SmilManifestConversionEngineType');
			
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
		if($jobType == BatchJobType::CONVERT && $jobSubType == self::getApiValue(SmilManifestConversionEngineType::SMIL_MANIFEST))
			return self::addFlavorParamsOutputForSourceAssets($jobData);
		else
			return $jobData;
	}

	public static function addFlavorParamsOutputForSourceAssets(vConvertJobData $jobData)
	{
		$assetsData = array();
		foreach($jobData->getSrcFileSyncs() as $srcFileSyncDesc)
		{
			/** @var $srcFileSyncDesc vSourceFileSyncDescriptor */
			$assetId = $srcFileSyncDesc->getAssetId();
			$flavorAsset = assetPeer::retrieveById($assetId);
			$assetsData['asset_'.$assetId.'_bitrate'] = $flavorAsset->getBitrate();
		}
		$pluginData = $jobData->getPluginData();
		if (!$pluginData)
			$pluginData = array();
		$jobData->setPluginData(array_merge($pluginData, $assetsData));
		return $jobData;
	}
}
