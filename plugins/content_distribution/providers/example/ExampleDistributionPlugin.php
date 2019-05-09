<?php
/**
 * @package plugins.exampleDistribution
 */
class ExampleDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'exampleDistribution';
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
			return array('ExampleDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('ExampleDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::EXAMPLE)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new ExampleDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new ExampleDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunExampleDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunExampleDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::EXAMPLE)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_ExampleProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
		{
			$reflect = new ReflectionClass('VidiunExampleDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(ExampleDistributionProviderType::EXAMPLE))
		{
			$reflect = new ReflectionClass('vExampleDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
			return new VidiunExampleDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
			return new ExampleDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::EXAMPLE)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'ExampleDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'ExampleDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunExampleDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunExampleDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::EXAMPLE)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_ExampleProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_ExampleDistribution_Type_ExampleDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
			return 'VidiunExampleDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(ExampleDistributionProviderType::EXAMPLE))
			return 'vExampleDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
			return 'VidiunExampleDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(ExampleDistributionProviderType::EXAMPLE))
			return 'ExampleDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return ExampleDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunExampleDistributionProvider();
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
