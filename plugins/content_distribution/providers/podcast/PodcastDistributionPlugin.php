<?php
/**
 * @package plugins.podcastDistribution
 */
class PodcastDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'podcastDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	const PODCAST_REPORT_HANDLER = 'vPodcastDistributionReportHandler';

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
			return array('PodcastDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('PodcastDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::PODCAST)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new PodcastDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new PodcastDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunPodcastDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunPodcastDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::PODCAST)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_PodcastProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
		{
			$reflect = new ReflectionClass('VidiunPodcastDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(PodcastDistributionProviderType::PODCAST))
		{
			$reflect = new ReflectionClass('vPodcastDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
			return new VidiunPodcastDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
			return new PodcastDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::PODCAST)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'PodcastDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'PodcastDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunPodcastDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunPodcastDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::PODCAST)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_PodcastProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_PodcastDistribution_Type_PodcastDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
			return 'VidiunPodcastDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(PodcastDistributionProviderType::PODCAST))
			return 'vPodcastDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
			return 'VidiunPodcastDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(PodcastDistributionProviderType::PODCAST))
			return 'PodcastDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return PodcastDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunPodcastDistributionProvider();
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
		// append PODCAST specific report statistics
		$status = $mrss->addChild('status');
		
		$status->addChild('emailed', $entryDistribution->getFromCustomData('emailed'));
		$status->addChild('rated', $entryDistribution->getFromCustomData('rated'));
		$status->addChild('blogged', $entryDistribution->getFromCustomData('blogged'));
		$status->addChild('reviewed', $entryDistribution->getFromCustomData('reviewed'));
		$status->addChild('bookmarked', $entryDistribution->getFromCustomData('bookmarked'));
		$status->addChild('playbackFailed', $entryDistribution->getFromCustomData('playbackFailed'));
		$status->addChild('timeSpent', $entryDistribution->getFromCustomData('timeSpent'));
		$status->addChild('recommended', $entryDistribution->getFromCustomData('recommended'));
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array();
		return array(
			self::PODCAST_REPORT_HANDLER,
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
