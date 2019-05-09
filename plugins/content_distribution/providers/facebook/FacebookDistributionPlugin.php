<?php
/**
 * @package plugins.facebookDistribution
 */
class FacebookDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'facebookDistribution';
	const CONTENT_DISTRIBUTION_VERSION_MAJOR = 1;
	const CONTENT_DISTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DISTRIBUTION_VERSION_BUILD = 0;
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function dependsOn()
	{
		$contentDistributionVersion = new VidiunVersion(
			self::CONTENT_DISTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DISTRIBUTION_VERSION_MINOR,
			self::CONTENT_DISTRIBUTION_VERSION_BUILD);
			
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
			return array('FacebookDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('FacebookDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FACEBOOK)
		{
			if(in_array($baseClass, array('IDistributionEngineSubmit', 'IDistributionEngineDelete', 'IDistributionEngineUpdate')))
				return new FacebookDistributionEngine();

			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunFacebookDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunFacebookDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FACEBOOK)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_FacebookProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
		{
			$reflect = new ReflectionClass('VidiunFacebookDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FacebookDistributionProviderType::FACEBOOK))
		{
			$reflect = new ReflectionClass('vFacebookDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
			return new VidiunFacebookDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
			return new FacebookDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FACEBOOK)
		{

			if(in_array($baseClass, array('IDistributionEngineSubmit', 'IDistributionEngineDelete', 'IDistributionEngineUpdate'))) {
				return 'FacebookDistributionEngine';
			}
					
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunFacebookDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunFacebookDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FACEBOOK)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_FacebookProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_FacebookDistribution_Type_FacebookDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
			return 'VidiunFacebookDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FacebookDistributionProviderType::FACEBOOK))
			return 'vFacebookDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
			return 'VidiunFacebookDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FacebookDistributionProviderType::FACEBOOK))
			return 'FacebookDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return FacebookDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunFacebookDistributionProvider();
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
	    $distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		if ($distributionProfile && $distributionProfile instanceof VidiunFacebookDistributionProfile)
		{
			$mrss->addChild(FacebookDistributionField::CALL_TO_ACTION_TYPE, $distributionProfile->getCallToActionType());
			$mrss->addChild(FacebookDistributionField::CALL_TO_ACTION_LINK, $distributionProfile->getCallToActionLink());
			$mrss->addChild(FacebookDistributionField::CALL_TO_ACTION_LINK_CAPTION, $distributionProfile->getCallToActionLinkCaption());
			$mrss->addChild(FacebookDistributionField::PLACE, $distributionProfile->getPlace());
			$mrss->addChild(FacebookDistributionField::TAGS, $distributionProfile->getTags());
			$mrss->addChild(FacebookDistributionField::TARGETING, $distributionProfile->getTargeting());
			$mrss->addChild(FacebookDistributionField::FEED_TARGETING, $distributionProfile->getFeedTargeting());
		}
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
}
