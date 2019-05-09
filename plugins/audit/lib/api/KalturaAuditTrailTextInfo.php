<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 */
class VidiunAuditTrailTextInfo extends VidiunAuditTrailInfo
{
	/**
	 * @var string
	 */
	public $info;
	
	private static $map_between_objects = array
	(
		"info",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @param vAuditTrailTextInfo $dbAuditTrail
	 * @param array $propsToSkip
	 * @return vAuditTrailInfo
	 */
	public function toObject($auditTrailInfo = null, $propsToSkip = array())
	{
		if(is_null($auditTrailInfo))
			$auditTrailInfo = new vAuditTrailTextInfo();
			
		return parent::toObject($auditTrailInfo, $propsToSkip);
	}
}
