<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 */
class VidiunAuditTrailChangeItem extends VidiunObject
{
	/**
	 * @var string
	 */
	public $descriptor;
	
	/**
	 * @var string
	 */
	public $oldValue;
	
	/**
	 * @var string
	 */
	public $newValue;

	
	private static $map_between_objects = array
	(
		"descriptor",
		"oldValue",
		"newValue",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * @param vAuditTrailChangeItem $dbAuditTrail
	 * @param array $propsToSkip
	 * @return vAuditTrailInfo
	 */
	public function toObject($auditTrailInfo = null, $propsToSkip = array())
	{
		if(is_null($auditTrailInfo))
			$auditTrailInfo = new vAuditTrailChangeItem();
			
		return parent::toObject($auditTrailInfo, $propsToSkip);
	}
}
