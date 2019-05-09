<?php
/**
 * @package plugins.FreewheelDistribution
 */
class FreewheelDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'freewheelDistribution';
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
			return array('FreewheelDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('FreewheelDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FREEWHEEL)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new FreewheelDistributionEngine();
		
			if($baseClass == 'IDistributionEngineEnable')
				return new FreewheelDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new FreewheelDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunFreewheelDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunFreewheelDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FREEWHEEL)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_FreewheelProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
		{
			$reflect = new ReflectionClass('VidiunFreewheelDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FreewheelDistributionProviderType::FREEWHEEL))
		{
			$reflect = new ReflectionClass('vFreewheelDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
			return new VidiunFreewheelDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
			return new FreewheelDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FREEWHEEL)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'FreewheelDistributionEngine';
		
			if($baseClass == 'IDistributionEngineEnable')
				return 'FreewheelDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'FreewheelDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunFreewheelDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunFreewheelDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FREEWHEEL)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_FreewheelProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_FreewheelDistribution_Type_FreewheelDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
			return 'VidiunFreewheelDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FreewheelDistributionProviderType::FREEWHEEL))
			return 'vFreewheelDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
			return 'VidiunFreewheelDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelDistributionProviderType::FREEWHEEL))
			return 'FreewheelDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return FreewheelDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunFreewheelDistributionProvider();
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
