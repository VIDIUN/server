<?php
/**
 * @package plugins.attUverseDistribution
 */
class AttUverseDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider, IVidiunEventConsumers, IVidiunServices
{
	const PLUGIN_NAME = 'attUverseDistribution';
	const ATT_UVERSE_EVENT_CONSUMER = 'vAttUverseDistributionEventConsumer';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;


	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function dependsOn()
	{
		$contentDistributionVersion = new VidiunVersion(
			self::CONTENT_DSTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DSTRIBUTION_VERSION_MINOR,
			self::CONTENT_DSTRIBUTION_VERSION_BUILD);
			
		$dependency = new VidiunDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		return array($dependency);
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
			
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(ContentDistributionPlugin::getPluginName());
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('AttUverseDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('AttUverseDistributionProviderType');
			
		return array();
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{			
		$objectClass = self::getObjectClass($baseClass, $enumValue);
		
		if (is_null($objectClass)) {
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
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// client side apps like batch and admin console
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::ATT_UVERSE)
		{										
			if($baseClass == 'IDistributionEngineSubmit')
				return 'AttUverseDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'AttUverseDistributionEngine';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::ATT_UVERSE)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_AttUverseProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_AttUverseDistribution_Type_AttUverseDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(AttUverseDistributionProviderType::ATT_UVERSE))
			return 'VidiunAttUverseDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(AttUverseDistributionProviderType::ATT_UVERSE))
			return 'vAttUverseDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(AttUverseDistributionProviderType::ATT_UVERSE))
			return 'VidiunAttUverseDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(AttUverseDistributionProviderType::ATT_UVERSE))
			return 'AttUverseDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return AttUverseDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunAttUverseDistributionProvider();
		$distributionProvider->fromObject(self::getProvider());
		return $distributionProvider;
	}
	
	/**
	 * Append provider specific nodes and attributes to the MRSS
	 * 
	 * @param EntryDistribution $entryDistribution
	 * @param SimpleXMLElement $mrss
	 */
	public static function contributeMRSS(EntryDistribution $entryDistribution, SimpleXMLElement $mrss)
	{
	    // append AttUverse specific report statistics
	    $distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		$mrss->addChild('channel_title', $distributionProfile->getChannelTitle());
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::ATT_UVERSE_EVENT_CONSUMER,
		);
	}
	
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDistributionProviderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DistributionProviderType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		return array(
			'attUverse' => 'AttUverseService'
		);
	}
}
