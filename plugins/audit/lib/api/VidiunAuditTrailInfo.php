<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 * @abstract
 */
abstract class VidiunAuditTrailInfo extends VidiunObject 
{
	/**
	 * @param vAuditTrailInfo $dbAuditTrail
	 * @param array $propsToSkip
	 * @return vAuditTrailInfo
	 */
	public function toObject($auditTrailInfo = null, $propsToSkip = array())
	{
		if(is_null($auditTrailInfo))
			$auditTrailInfo = new vAuditTrailInfo();
			
		return parent::toObject($auditTrailInfo, $propsToSkip);
	}
}
