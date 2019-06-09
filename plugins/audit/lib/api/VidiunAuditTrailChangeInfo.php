<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 */
class VidiunAuditTrailChangeInfo extends VidiunAuditTrailInfo
{
	/**
	 * @var VidiunAuditTrailChangeItemArray
	 */
	public $changedItems;

	/**
	 * @param vAuditTrailChangeInfo $dbAuditTrail
	 * @param array $propsToSkip
	 * @return vAuditTrailInfo
	 */
	public function toObject($auditTrailInfo = null, $propsToSkip = array())
	{
		if(is_null($auditTrailInfo))
			$auditTrailInfo = new vAuditTrailChangeInfo();
			
		$auditTrailInfo = parent::toObject($auditTrailInfo, $propsToSkip);
		$auditTrailInfo->setChangedItems($this->changedItems->toObjectArray());
		
		return $auditTrailInfo;
	}

	/**
	 * @param vAuditTrailChangeInfo $auditTrailInfo
	 */
	public function doFromObject($auditTrailInfo, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($auditTrailInfo, $responseProfile);
		
		if($this->shouldGet('changedItems', $responseProfile))
			$this->changedItems = VidiunAuditTrailChangeItemArray::fromDbArray($auditTrailInfo->getChangedItems());
	}
}
