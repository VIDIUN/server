<?php
/**
 * @package plugins.cielo24
 */
class Cielo24Plugin extends IntegrationProviderPlugin implements IVidiunEventConsumers
{
	const PLUGIN_NAME = 'cielo24';
	const FLOW_MANAGER = 'vCielo24FlowManager';
	
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
		return 'Cielo24IntegrationProviderType';
	}
	
	/*
	 * @return IIntegrationProvider
	 */
	public function getProvider()
	{
		return new IntegrationCielo24Provider(); 
	}
	
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'vIntegrationJobProviderData' && $enumValue == self::getApiValue(Cielo24IntegrationProviderType::CIELO24))
		{
			return 'vCielo24JobProviderData';
		}
	
		if($baseClass == 'VidiunIntegrationJobProviderData')
		{
			if($enumValue == self::getApiValue(Cielo24IntegrationProviderType::CIELO24) || $enumValue == self::getIntegrationProviderCoreValue(Cielo24IntegrationProviderType::CIELO24))
				return 'VidiunCielo24JobProviderData';
		}
	
		if($baseClass == 'VIntegrationEngine' || $baseClass == 'VIntegrationCloserEngine')
		{
			if($enumValue == VidiunIntegrationProviderType::CIELO24)
				return 'VCielo24IntegrationEngine';
		}
		if($baseClass == 'IIntegrationProvider' && $enumValue == self::getIntegrationProviderCoreValue(Cielo24IntegrationProviderType::CIELO24))
		{
			return 'IntegrationCielo24Provider';
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
	
	public static function getClientHelper($username, $password, $baseUrl = null, $additionalParams = array())
	{
		return new Cielo24ClientHelper($username, $password, $baseUrl, $additionalParams);
	}
	
	/**
	 * @return Cielo24Options
	 */	
	public static function getPartnerCielo24Options($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return null;
		return $partner->getFromCustomData(Cielo24IntegrationProviderType::CIELO24);
	}
	
	public static function setPartnerCielo24Options($partnerId, Cielo24Options $options)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return;
		$partner->putInCustomData(Cielo24IntegrationProviderType::CIELO24, $options);
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
			$enums[] = 'Cielo24TranscriptProviderType';
			return $enums;
		}
		
		if ($baseEnumName == 'TranscriptProviderType')
		{
			return array ('Cielo24TranscriptProviderType');
		}
		
		return $enums;
	}
}
