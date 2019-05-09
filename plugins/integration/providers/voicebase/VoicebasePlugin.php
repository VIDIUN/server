<?php
/**
 * @package plugins.voicebase
 */
class VoicebasePlugin extends IntegrationProviderPlugin implements IVidiunEventConsumers
{
	const PLUGIN_NAME = 'voicebase';
	const FLOW_MANAGER = 'vVoicebaseFlowManager';
	
	const INTEGRATION_PLUGIN_VERSION_MAJOR = 1;
	const INTEGRATION_PLUGIN_VERSION_MINOR = 0;
	const INTEGRATION_PLUGIN_VERSION_BUILD = 0;
	
	const TRANSCRIPT_PLUGIN_VERSION_MAJOR = 1;
	const TRANSCRIPT_PLUGIN_VERSION_MINOR = 0;
	const TRANSCRIPT_PLUGIN_VERSION_BUILD = 0;
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$transcriptVersion = new VidiunVersion(
			self::TRANSCRIPT_PLUGIN_VERSION_MAJOR,
			self::TRANSCRIPT_PLUGIN_VERSION_MINOR,
			self::TRANSCRIPT_PLUGIN_VERSION_BUILD
		);
		$transcriptDependency = new VidiunDependency(TranscriptPlugin::getPluginName(), $transcriptVersion);

		return array_merge(parent::dependsOn(), array($transcriptDependency));
	}
	
	/* (non-PHPdoc)
	 * @see IntegrationProviderPlugin::getRequiredIntegrationPluginVersion()
	 */
	public static function getRequiredIntegrationPluginVersion()
	{
		return new VidiunVersion(
			self::INTEGRATION_PLUGIN_VERSION_MAJOR,
			self::INTEGRATION_PLUGIN_VERSION_MINOR,
			self::INTEGRATION_PLUGIN_VERSION_BUILD
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::FLOW_MANAGER,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IntegrationProviderPlugin::getIntegrationProviderClassName()
	 */
	public static function getIntegrationProviderClassName()
	{
		return 'VoicebaseIntegrationProviderType';
	}

	/*
	 * @return IIntegrationProvider
	 */
	public function getProvider()
	{
		return new IntegrationVoicebaseProvider(); 
	}
	
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'vIntegrationJobProviderData' && $enumValue == self::getApiValue(VoicebaseIntegrationProviderType::VOICEBASE))
		{
			return 'vVoicebaseJobProviderData';
		}
	
		if($baseClass == 'VidiunIntegrationJobProviderData')
		{
			if($enumValue == self::getApiValue(VoicebaseIntegrationProviderType::VOICEBASE) || $enumValue == self::getIntegrationProviderCoreValue(VoicebaseIntegrationProviderType::VOICEBASE))
				return 'VidiunVoicebaseJobProviderData';
		}
	
		if($baseClass == 'VIntegrationEngine' || $baseClass == 'VIntegrationCloserEngine')
		{
			if($enumValue == VidiunIntegrationProviderType::VOICEBASE)
				return 'VVoicebaseIntegrationEngine';
		}
		if($baseClass == 'IIntegrationProvider' && $enumValue == self::getIntegrationProviderCoreValue(VoicebaseIntegrationProviderType::VOICEBASE))
		{
			return 'IntegrationVoicebaseProvider';
		}
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getProviderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('IntegrationProviderType', $value);
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getTranscriptProviderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('TranscriptProviderType', $value);
	}
	
	public static function getClientHelper($apiKey, $apiPassword, $additionalParams = null)
	{
		return new VoicebaseClientHelper($apiKey, $apiPassword, $additionalParams);
	}
	
	/**
	 * @return VoicebaseOptions
	 */	
	public static function getPartnerVoicebaseOptions($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return null;
		return $partner->getFromCustomData(VoicebaseIntegrationProviderType::VOICEBASE);
	}
	
	public static function setPartnerVoicebaseOptions($partnerId, VoicebaseOptions $options)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return;
		$partner->putInCustomData(VoicebaseIntegrationProviderType::VOICEBASE, $options);
		$partner->save();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		$enums = parent::getEnums($baseEnumName);
		
		if (is_null ($baseEnumName))
		{
			$enums[] = 'VoicebaseTranscriptProviderType';
			return $enums;
		}
		
		if ($baseEnumName == 'TranscriptProviderType')
		{
			return array ('VoicebaseTranscriptProviderType');
		}
		
		return $enums;
	}
}
