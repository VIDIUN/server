<?php
/**
 * @package plugins.msnDistribution
 */
class MsnDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'msnDistribution';
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
			return array('MsnDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('MsnDistributionProviderType');
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::MSN)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new MsnDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new MsnDistributionEngine();
		
			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunMsnDistributionProfile();
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunMsnDistributionJobProviderData();
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::MSN)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_MsnProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		// content distribution does not work in partner services 2 context because it uses dynamic enums
		if (!class_exists('vCurrentContext') || vCurrentContext::$ps_vesion != 'ps3')
			return null;

		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
		{
			$reflect = new ReflectionClass('VidiunMsnDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(MsnDistributionProviderType::MSN))
		{
			$reflect = new ReflectionClass('vMsnDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
			return new VidiunMsnDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
			return new MsnDistributionProfile();
			
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
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::MSN)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'MsnDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'MsnDistributionEngine';
		
			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunMsnDistributionProfile';
		
			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunMsnDistributionJobProviderData';
		}
		
		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::MSN)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_MsnProfileConfiguration';
				
			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_MsnDistribution_Type_MsnDistributionProfile';
		}
		
		// content distribution does not work in partner services 2 context because it uses dynamic enums
		if (!class_exists('vCurrentContext') || vCurrentContext::$ps_vesion != 'ps3')
			return null;

		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
			return 'VidiunMsnDistributionJobProviderData';
	
		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(MsnDistributionProviderType::MSN))
			return 'vMsnDistributionJobProviderData';
	
		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
			return 'VidiunMsnDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN))
			return 'MsnDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return MsnDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunMsnDistributionProvider();
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
	    // append Msn specific report statistics
	    $distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
	    if (!$distributionProfile)
	    	return VidiunLog::err('Distribution profile #'.$entryDistribution->getDistributionProfileId() .' not found');

    	if (!$distributionProfile instanceof MsnDistributionProfile)
    		return VidiunLog::crit('Distribution profile #'.$entryDistribution->getDistributionProfileId() .' is not instanceof MsnDistributionProfile');
    		
		$mrss->addChild('csid', $distributionProfile->getCsId());
		$mrss->addChild('source', $distributionProfile->getSource());
		$mrss->addChild('source_friendly_name', $distributionProfile->getSourceFriendlyName());
		$mrss->addChild('page_group', $distributionProfile->getPageGroup());
		$mrss->addChild('msnvideo_cat', $distributionProfile->getMsnvideoCat());
		$mrss->addChild('msnvideo_top', $distributionProfile->getMsnvideoTop());
		$mrss->addChild('msnvideo_top_cat', $distributionProfile->getMsnvideoTopCat());
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
