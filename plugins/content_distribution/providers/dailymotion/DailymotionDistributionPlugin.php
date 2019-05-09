<?php
/**
 * @package plugins.dailymotionDistribution
 */
class DailymotionDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'dailymotionDistribution';
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
			return array('DailymotionDistributionProviderType');
			
		if($baseEnumName == 'DistributionProviderType')
			return array('DailymotionDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::DAILYMOTION)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineEnable')
				return new DailymotionDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new DailymotionDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunDailymotionDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunDailymotionDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::DAILYMOTION)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_DailymotionProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
		{
			$reflect = new ReflectionClass('VidiunDailymotionDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(DailymotionDistributionProviderType::DAILYMOTION))
		{
			$reflect = new ReflectionClass('vDailymotionDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
			return new VidiunDailymotionDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
			return new DailymotionDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::DAILYMOTION)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineEnable')
				return 'DailymotionDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'DailymotionDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunDailymotionDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunDailymotionDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::DAILYMOTION)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_DailymotionProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_DailymotionDistribution_Type_DailymotionDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
			return 'VidiunDailymotionDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(DailymotionDistributionProviderType::DAILYMOTION))
			return 'vDailymotionDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
			return 'VidiunDailymotionDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(DailymotionDistributionProviderType::DAILYMOTION))
			return 'DailymotionDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return DailymotionDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunDailymotionDistributionProvider();
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
