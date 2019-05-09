<?php
/**
 * @package plugins.youTubeDistribution
 */
class YouTubeDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'youTubeDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	const YOUTUBE_EVENT_CONSUMER = 'vYouTubeDistributionEventConsumer';
	
	const GOOGLE_APP_ID = 'youtubepartner';

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
			return array('YouTubeDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('YouTubeDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::YOUTUBE)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineReport')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new YouTubeDistributionEngineSelector();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new YouTubeDistributionEngineSelector();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunYouTubeDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunYouTubeDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::YOUTUBE)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_YouTubeProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
		{
			$reflect = new ReflectionClass('VidiunYouTubeDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(YouTubeDistributionProviderType::YOUTUBE))
		{
			$reflect = new ReflectionClass('vYouTubeDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
			return new VidiunYouTubeDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
			return new YouTubeDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::YOUTUBE)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'YouTubeDistributionEngineSelector';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'YouTubeDistributionEngineSelector';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunYouTubeDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunYouTubeDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::YOUTUBE)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_YouTubeProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_YouTubeDistribution_Type_YouTubeDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
			return 'VidiunYouTubeDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(YouTubeDistributionProviderType::YOUTUBE))
			return 'vYouTubeDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
			return 'VidiunYouTubeDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(YouTubeDistributionProviderType::YOUTUBE))
			return 'YouTubeDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return YouTubeDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunYouTubeDistributionProvider();
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
		$mrss->addChild('allow_comments', $distributionProfile->getAllowComments());
		$mrss->addChild('allow_responses', $distributionProfile->getAllowResponses());
		$mrss->addChild('allow_ratings', $distributionProfile->getAllowRatings());
		$mrss->addChild('allow_embedding', $distributionProfile->getAllowEmbedding());
		$mrss->addChild('commerical_policy', $distributionProfile->getCommercialPolicy());
		$mrss->addChild('ugc_policy', $distributionProfile->getUgcPolicy());
		$mrss->addChild('default_category', $distributionProfile->getDefaultCategory());
		$mrss->addChild('target', $distributionProfile->getTarget());
		$mrss->addChild('notification_email', $distributionProfile->getNotificationEmail());
		$mrss->addChild('account_username', $distributionProfile->getUsername());
		$mrss->addChild('ad_server_partner_id', $distributionProfile->getAdServerPartnerId());
		$mrss->addChild('allow_pre_roll_ads', $distributionProfile->getAllowPreRollAds());
		$mrss->addChild('allow_post_roll_ads', $distributionProfile->getAllowPostRollAds());		
		$mrss->addChild('claim_type', $distributionProfile->getClaimType());
		$mrss->addChild('instream_standard', $distributionProfile->getInstreamStandard());
		$mrss->addChild('privacy_status', $distributionProfile->getPrivacyStatus());
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::YOUTUBE_EVENT_CONSUMER,
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
}
