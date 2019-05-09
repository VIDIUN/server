<?php
/**
 * @package plugins.youtubeApiDistribution
 */
class YoutubeApiDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'youtubeApiDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	const GOOGLE_APP_ID = 'youtubeapi';
	
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
			return array('YoutubeApiDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('YoutubeApiDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::YOUTUBE_API)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineEnable')
				return new YoutubeApiDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new YoutubeApiDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunYoutubeApiDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunYoutubeApiDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::YOUTUBE_API)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_YoutubeApiProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
		{
			$reflect = new ReflectionClass('VidiunYoutubeApiDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
		{
			$reflect = new ReflectionClass('vYoutubeApiDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return new VidiunYoutubeApiDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return new YoutubeApiDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::YOUTUBE_API)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineEnable')
				return 'YoutubeApiDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'YoutubeApiDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunYoutubeApiDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunYoutubeApiDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::YOUTUBE_API)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_YoutubeApiProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_YoutubeApiDistribution_Type_YoutubeApiDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return 'VidiunYoutubeApiDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return 'vYoutubeApiDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return 'VidiunYoutubeApiDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YoutubeApiDistributionProviderType::YOUTUBE_API))
			return 'YoutubeApiDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return YoutubeApiDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunYoutubeApiDistributionProvider();
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
	    // append YouTube specific report statistics
	    $distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		$mrss->addChild('account_username', $distributionProfile->getUsername());
		$mrss->addChild('default_category', $distributionProfile->getDefaultCategory());
	    $mrss->addChild('allow_comments', $distributionProfile->getAllowComments());
		$mrss->addChild('allow_responses', $distributionProfile->getAllowResponses());
		$mrss->addChild('allow_ratings', $distributionProfile->getAllowRatings());
		$mrss->addChild('allow_embedding', $distributionProfile->getAllowEmbedding());
		$mrss->addChild('privacy_status', $distributionProfile->getPrivacyStatus());
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
