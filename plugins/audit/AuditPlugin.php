<?php
/**
 * @package plugins.audit
 */
class AuditPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunServices, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'audit';
	const AUDIT_TRAIL_MANAGER = 'vAuditTrailManager';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'auditTrail' => 'AuditTrailService',
		);
		return $map;
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::AUDIT_TRAIL_MANAGER,
		);
	}
}
