<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 */
class VidiunAuditTrailFileSyncCreateInfo extends VidiunAuditTrailInfo
{
	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var int
	 */
	public $objectSubType;

	/**
	 * @var int
	 */
	public $dc;

	/**
	 * @var bool
	 */
	public $original;

	/**
	 * @var VidiunAuditTrailFileSyncType
	 */
	public $fileType;

	
	private static $map_between_objects = array
	(
		"version",
		"objectSubType",
		"dc",
		"original",
		"fileType",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @param vAuditTrailFileSyncCreateInfo $dbAuditTrail
	 * @param array $propsToSkip
	 * @return vAuditTrailInfo
	 */
	public function toObject($auditTrailInfo = null, $propsToSkip = array())
	{
		if(is_null($auditTrailInfo))
			$auditTrailInfo = new vAuditTrailFileSyncCreateInfo();
			
		return parent::toObject($auditTrailInfo, $propsToSkip);
	}
}
