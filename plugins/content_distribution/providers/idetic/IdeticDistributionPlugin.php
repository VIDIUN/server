<?php
/**
 * @package plugins.ideticDistribution
 */
class IdeticDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'ideticDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	const IDETIC_REPORT_HANDLER = 'vIdeticDistributionReportHandler';

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
			return array('IdeticDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('IdeticDistributionProviderType');
			
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

		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::IDETIC)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new IdeticDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new IdeticDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunIdeticDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunIdeticDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::IDETIC)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_IdeticProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
		{
			$reflect = new ReflectionClass('VidiunIdeticDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(IdeticDistributionProviderType::IDETIC))
		{
			$reflect = new ReflectionClass('vIdeticDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
			return new VidiunIdeticDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
			return new IdeticDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::IDETIC)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'IdeticDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'IdeticDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunIdeticDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunIdeticDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::IDETIC)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_IdeticProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_IdeticDistribution_Type_IdeticDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
			return 'VidiunIdeticDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(IdeticDistributionProviderType::IDETIC))
			return 'vIdeticDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
			return 'VidiunIdeticDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(IdeticDistributionProviderType::IDETIC))
			return 'IdeticDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return IdeticDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunIdeticDistributionProvider();
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
		// append IDETIC specific report statistics
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array();
//		return array(
//			self::IDETIC_REPORT_HANDLER,
//		);
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
