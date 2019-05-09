<?php
/**
 * @package plugins.virusScan
 */
class VirusScanPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunServices, IVidiunEventConsumers, IVidiunEnumerator, IVidiunObjectLoader, IVidiunAdminConsolePages 
{
	const PLUGIN_NAME = 'virusScan';
	const VIRUS_SCAN_FLOW_MANAGER_CLASS = 'vVirusScanFlowManager';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
		
		if($partnerId == Partner::BATCH_PARTNER_ID)
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if($partner)
			return $partner->getPluginEnabled(self::PLUGIN_NAME);
	
		return false;
	}
	
	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'virusScanProfile' => 'VirusScanProfileService',
		);
		return $map;
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::VIRUS_SCAN_FLOW_MANAGER_CLASS
		);
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('VirusScanEntryStatus', 'VirusScanBatchJobType');
			
		if($baseEnumName == 'entryStatus')
			return array('VirusScanEntryStatus');
			
		if($baseEnumName == 'BatchJobType')
			return array('VirusScanBatchJobType');
			
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
		// virus scan only works in api_v3 context because it uses dynamic enums
		if (!class_exists('vCurrentContext') || !vCurrentContext::isApiV3Context())
			return null;
			
		if($baseClass == 'vJobData')
		{
			if($enumValue == self::getBatchJobTypeCoreValue(VirusScanBatchJobType::VIRUS_SCAN))
			{
				return new vVirusScanJobData();
			}
		}
	
		if($baseClass == 'VidiunJobData')
		{
			if($enumValue == self::getApiValue(VirusScanBatchJobType::VIRUS_SCAN) || 
			   $enumValue == self::getBatchJobTypeCoreValue(VirusScanBatchJobType::VIRUS_SCAN))
			{
				return new VidiunVirusScanJobData();
			}
		}
		
		return null;
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// virus scan only works in api_v3 context because it uses dynamic enums
		if (!class_exists('vCurrentContext') || !vCurrentContext::isApiV3Context())
			return null;
			
		if($baseClass == 'vJobData')
		{
			if($enumValue == self::getBatchJobTypeCoreValue(VirusScanBatchJobType::VIRUS_SCAN))
			{
				return 'vVirusScanJobData';
			}
		}
	
		if($baseClass == 'VidiunJobData')
		{
			if($enumValue == self::getApiValue(VirusScanBatchJobType::VIRUS_SCAN))
			{
				return 'VidiunVirusScanJobData';
			}
		}
		
		return null;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBatchJobTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BatchJobType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getEntryStatusCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('entryStatus', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new VirusScanListAction();
		$pages[] = new VirusScanConfigureAction();
		$pages[] = new VirusScanSetStatusAction();
		return $pages;
	}
}
