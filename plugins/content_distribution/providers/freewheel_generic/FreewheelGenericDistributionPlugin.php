<?php
/**
 * @package plugins.freewheelGenericDistribution
 */
class FreewheelGenericDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'freewheelGenericDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;

	const DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT = 'cuePoint';

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

		$dependency1 = new VidiunDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		$dependency2 = new VidiunDependency(FreewheelGenericDistributionPlugin::DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT);
		return array($dependency1, $dependency2);
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
			return array('FreewheelGenericDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('FreewheelGenericDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FREEWHEEL_GENERIC)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new FreewheelGenericDistributionEngine();
		
			if($baseClass == 'IDistributionEngineEnable')
				return new FreewheelGenericDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new FreewheelGenericDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunFreewheelGenericDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunFreewheelGenericDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FREEWHEEL_GENERIC)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_FreewheelGenericProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
		{
			$reflect = new ReflectionClass('VidiunFreewheelGenericDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
		{
			$reflect = new ReflectionClass('vFreewheelGenericDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return new VidiunFreewheelGenericDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return new FreewheelGenericDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::FREEWHEEL_GENERIC)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'FreewheelGenericDistributionEngine';
		
			if($baseClass == 'IDistributionEngineEnable')
				return 'FreewheelGenericDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'FreewheelGenericDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunFreewheelGenericDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunFreewheelGenericDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FREEWHEEL_GENERIC)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_FreewheelGenericProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_FreewheelGenericDistribution_Type_FreewheelGenericDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return 'VidiunFreewheelGenericDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return 'vFreewheelGenericDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return 'VidiunFreewheelGenericDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(FreewheelGenericDistributionProviderType::FREEWHEEL_GENERIC))
			return 'FreewheelGenericDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return FreewheelGenericDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunFreewheelGenericDistributionProvider();
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
