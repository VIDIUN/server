<?php
/**
 * @package plugins.ismIndex
 */
class IsmIndexPlugin extends VidiunPlugin implements IVidiunObjectLoader, IVidiunEnumerator, IVidiunEventConsumers, IVidiunBatchJobDataContributor
{
	const PLUGIN_NAME = 'ismIndex';
	const ISM_INDEX_EVENTS_CONSUMER = 'vIsmIndexEventsConsumer';
	
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
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::ISMINDEX)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			$params = $constructorArgs['params'];
			return new VOperationEngineIsmIndex($params->ismIndexCmd, $constructorArgs['outFilePath']);
		}
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(IsmIndexConversionEngineType::ISMINDEX))
		{
			return new VDLOperatorIsmIndex($enumValue);
		}
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::ISM_MANIFEST)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
				
			$params = $constructorArgs['params'];
			return new VOperationEngineIsmManifest($params->ismIndexCmd, $constructorArgs['outFilePath']);
		}
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(IsmIndexConversionEngineType::ISM_MANIFEST))
		{
			return new VDLOperatorIsmManifest($enumValue);
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
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(IsmIndexConversionEngineType::ISMINDEX))
			return 'VOperationEngineIsmIndex';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(IsmIndexConversionEngineType::ISMINDEX))
			return 'VDLOperatorIsmIndex';
			
		if($baseClass == 'VOperationEngine' && $enumValue == self::getApiValue(IsmIndexConversionEngineType::ISM_MANIFEST))
			return 'VOperationEngineIsmManifest';
	
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getConversionEngineCoreValue(IsmIndexConversionEngineType::ISM_MANIFEST))
			return 'VDLOperatorIsmManifest';
		
			
		
		return null;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('IsmIndexConversionEngineType');
	
		if($baseEnumName == 'conversionEngineType')
			return array('IsmIndexConversionEngineType');
			
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
		if($jobType == BatchJobType::CONVERT && $jobSubType == self::getApiValue(IsmIndexConversionEngineType::ISM_MANIFEST))
			return self::addIsmManifestsToSrcFileSyncDesc($jobData);
		else
			return $jobData;
	}
	
	public static function addIsmManifestsToSrcFileSyncDesc(vConvertJobData $jobData)
	{
		$additionalFileSyncs = array();
		foreach ($jobData->getSrcFileSyncs() as $srcFileSyncDesc) 
		{
			$ismDescriptor = self::getFileSyncDescriptor($srcFileSyncDesc, flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISM);
			$ismcDescriptor = self::getFileSyncDescriptor($srcFileSyncDesc, flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISMC);
			if($ismDescriptor && $ismcDescriptor)
			{
				$additionalFileSyncs[] = $ismDescriptor;
				$additionalFileSyncs[] = $ismcDescriptor;
			}								
		}
		
		$jobData->setSrcFileSyncs(array_merge($jobData->getSrcFileSyncs(), $additionalFileSyncs));
		return $jobData;
		
	}
	
	private static function getFileSyncDescriptor(vSourceFileSyncDescriptor $flavorAssetDesc, $objectSubType)
	{
		$ismDescriptor = null;
		$flavorAsset = assetPeer::retrieveById($flavorAssetDesc->getAssetId());
		$key = $flavorAsset->getSyncKey($objectSubType);			
		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($key);
		if($fileSync)
		{
			$ismDescriptor = new vSourceFileSyncDescriptor();
			$ismDescriptor->setPathAndKeyByFileSync($fileSync);
			$ismDescriptor->setFileSyncRemoteUrl($fileSync->getExternalUrl($flavorAsset->getEntryId()));
			$ismDescriptor->setAssetId($key->getObjectId());
			$ismDescriptor->setAssetParamsId($flavorAssetDesc->getAssetParamsId());
			$ismDescriptor->setFileSyncObjectSubType($key->getObjectSubType());
		}

		return $ismDescriptor;
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::ISM_INDEX_EVENTS_CONSUMER,
		);
	}
}
