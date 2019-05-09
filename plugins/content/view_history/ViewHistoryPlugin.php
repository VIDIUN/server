<?php
/**
 * Plugin enabling the storage of user view history
 * @package plugins.viewHistory
 */
 class ViewHistoryPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunObjectLoader
 {
 	const PLUGIN_NAME = "viewHistory";
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		if (in_array($partnerId, array(Partner::ADMIN_CONSOLE_PARTNER_ID, Partner::BATCH_PARTNER_ID)))
			return true;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if (is_null($baseEnumName))
			return array('ViewHistoryExtendedStatus','ViewHistoryUserEntryType');
		if ($baseEnumName == 'UserEntryExtendedStatus')
			return array('ViewHistoryExtendedStatus');
		if ($baseEnumName == 'UserEntryType')
			return array('ViewHistoryUserEntryType');
		
		return array();
	}
	
	public static function getViewHistoryUserEntryTypeCoreValue ($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('UserEntryType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if ( ($baseClass == "VidiunUserEntry") && ($enumValue == self::getViewHistoryUserEntryTypeCoreValue(ViewHistoryUserEntryType::VIEW_HISTORY)))
		{
			return new VidiunViewHistoryUserEntry();
		}
		if ( ($baseClass == "UserEntry") && ($enumValue == self::getViewHistoryUserEntryTypeCoreValue(ViewHistoryUserEntryType::VIEW_HISTORY)))
		{
			return new ViewHistoryUserEntry();
		}
	}
	
	public static function getObjectClass($baseClass, $enumValue)
	{
		if ($baseClass == 'UserEntry' && $enumValue == self::getViewHistoryUserEntryTypeCoreValue(ViewHistoryUserEntryType::VIEW_HISTORY))
		{
			return 'ViewHistoryUserEntry';
		}
	}
 }