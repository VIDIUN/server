<?php
/**
 * @package plugins.ftpDistribution
 */
class FtpDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'ftpDistribution';
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
			return array('FtpDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('FtpDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && ($enumValue == VidiunDistributionProviderType::FTP || $enumValue == VidiunDistributionProviderType::FTP_SCHEDULED))
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new FtpDistributionEngine();
		
			if($baseClass == 'IDistributionEngineEnable')
				return new FtpDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new FtpDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunFtpDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunFtpDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && ($enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FTP || $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FTP_SCHEDULED))
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_FtpProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}

		if($baseClass == 'VidiunDistributionJobProviderData' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
		{
			$reflect = new ReflectionClass('VidiunFtpDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && ($enumValue == self::getApiValue(FtpDistributionProviderType::FTP) || $enumValue == self::getApiValue(FtpDistributionProviderType::FTP_SCHEDULED)))
		{
			$reflect = new ReflectionClass('vFtpDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return new VidiunFtpDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return new FtpDistributionProfile();
			
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
		if (class_exists('VidiunClient') && ($enumValue == VidiunDistributionProviderType::FTP || $enumValue == VidiunDistributionProviderType::FTP_SCHEDULED))
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'FtpDistributionEngine';
		
			if($baseClass == 'IDistributionEngineEnable')
				return 'FtpDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'FtpDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunFtpDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunFtpDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && ($enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FTP || $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::FTP_SCHEDULED))
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_FtpProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_FtpDistribution_Type_FtpDistributionProfile';
		}
		
		if($baseClass == 'VidiunDistributionJobProviderData' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return 'VidiunFtpDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && ($enumValue == self::getApiValue(FtpDistributionProviderType::FTP) || $enumValue == self::getApiValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return 'vFtpDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return 'VidiunFtpDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && ($enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP) || $enumValue == self::getDistributionProviderTypeCoreValue(FtpDistributionProviderType::FTP_SCHEDULED)))
			return 'FtpDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return FtpDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunFtpDistributionProvider();
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
