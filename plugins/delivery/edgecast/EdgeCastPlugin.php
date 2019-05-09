<?php
/**
 * @package plugins.edgeCast
 */
class EdgeCastPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunEventConsumers, IVidiunEnumerator, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'edgeCast';
	const EDGECAST_FLOW_MANAGER = 'vEdgeCastFlowManager';
	const PARTNER_CUSTOM_DATA_FIELD_EDGECAST_PARAMS = 'edgeCastParams';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);		
	}
			
	public static function getEventConsumers(){
		return array(self::EDGECAST_FLOW_MANAGER);
	}
	
	/**
	 * @param Partner $partner
	 * @return vEdgeCastParams
	 */
	public static function getEdgeCastParams($partner)
	{
		return $partner->getFromCustomData(self::getPluginName().'_'.self::PARTNER_CUSTOM_DATA_FIELD_EDGECAST_PARAMS);
	}
	
	/**
	 * @param Partner $partner
	 * @param vEdgeCastParams $edgeCastParams
	 */
	public static function setEdgeCastParams($partner, $edgeCastParams)
	{		
		$partner->putInCustomData(self::getPluginName().'_'.self::PARTNER_CUSTOM_DATA_FIELD_EDGECAST_PARAMS, $edgeCastParams);
	}	
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDeliveryProfileType($valueName)
	{
		$apiValue = self::getApiValue($valueName);
		return vPluginableEnumsManager::apiToCore('DeliveryProfileType', $apiValue);
	}
	
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('EdgeCastDeliveryProfileType');
		if($baseEnumName == 'DeliveryProfileType')
			return array('EdgeCastDeliveryProfileType');
			
		return array();
	}
	
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null) {
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	*/
	public static function getObjectClass($baseClass, $enumValue) {
		if ($baseClass == 'DeliveryProfile') {
			if($enumValue == self::getDeliveryProfileType(EdgeCastDeliveryProfileType::EDGE_CAST_HTTP))
				return 'DeliveryProfileEdgeCastHttp';
			if($enumValue == self::getDeliveryProfileType(EdgeCastDeliveryProfileType::EDGE_CAST_RTMP))
				return 'DeliveryProfileEdgeCastRtmp';
		}
		return null;
	}
}
