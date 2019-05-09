<?php
/**
 * @package plugins.comcastMrssDistribution
 */
class ComcastMrssDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider, IVidiunEventConsumers, IVidiunServices
{
	const PLUGIN_NAME = 'comcastMrssDistribution';
	const COMCAST_MRSS_EVENT_CONSUMER = "vComcastMrssFlowManager";
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 1;
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
			
		$dependencyDistribution = new VidiunDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		$dependencyCaptions = new VidiunDependency(CaptionPlugin::PLUGIN_NAME);
		$dependencyCuePoints = new VidiunDependency(CuePointPlugin::PLUGIN_NAME);
		
		return array($dependencyDistribution, $dependencyCaptions, $dependencyCuePoints);
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
			return array('ComcastMrssDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('ComcastMrssDistributionProviderType');
			
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
		// client side apps like batch and admin console
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::COMCAST_MRSS)
		{
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunComcastMrssDistributionProfile();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::COMCAST_MRSS)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_ComcastMrssProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ComcastMrssDistributionProviderType::COMCAST_MRSS))
			return new VidiunComcastMrssDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ComcastMrssDistributionProviderType::COMCAST_MRSS))
			return new ComcastMrssDistributionProfile();
			
		return null;
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// client side apps like batch and admin console
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::COMCAST_MRSS)
		{
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunComcastMrssDistributionProfile';
		} 
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::COMCAST_MRSS)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_ComcastMrssProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_ComcastMrssDistribution_Type_ComcastMrssDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ComcastMrssDistributionProviderType::COMCAST_MRSS))
			return 'VidiunComcastMrssDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ComcastMrssDistributionProviderType::COMCAST_MRSS))
			return 'ComcastMrssDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return ComcastMrssDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunComcastMrssDistributionProvider();
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
		// append comcast mrss specific configuration
		$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		/* @var $distributionProfile ComcastMrssDistributionProfile */ 
		$mrss->addChild('feed_link', $distributionProfile->getFeedLink());
		$mrss->addChild('item_link', $distributionProfile->getItemLink());
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
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::COMCAST_MRSS_EVENT_CONSUMER,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		return array(
			'comcastMrss' => 'ComcastMrssService'
		);
	}
}
