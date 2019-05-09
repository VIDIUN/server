<?php
/**
 * The Audit Trail service allows you to keep track of changes made to various Vidiun objects. 
 * This service is disabled by default.
 *
 * @service auditTrail
 * @package plugins.audit
 * @subpackage api.services
 */
class AuditTrailService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$this->applyPartnerFilterForClass('AuditTrail');
		$this->applyPartnerFilterForClass('AuditTrailData');
		$this->applyPartnerFilterForClass('AuditTrailConfig');
		
		if(!AuditPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, AuditPlugin::PLUGIN_NAME);
	}
	
	/**
	 * Allows you to add an audit trail object and audit trail content associated with Vidiun object
	 * 
	 * @action add
	 * @param VidiunAuditTrail $auditTrail
	 * @return VidiunAuditTrail
	 * @throws AuditTrailErrors::AUDIT_TRAIL_DISABLED
	 */
	function addAction(VidiunAuditTrail $auditTrail)
	{
		$auditTrail->validatePropertyNotNull("auditObjectType");
		$auditTrail->validatePropertyNotNull("objectId");
		$auditTrail->validatePropertyNotNull("action");
		$auditTrail->validatePropertyMaxLength("description", 1000);
		
		$dbAuditTrail = $auditTrail->toInsertableObject();
		$dbAuditTrail->setPartnerId($this->getPartnerId());
		$dbAuditTrail->setStatus(AuditTrail::AUDIT_TRAIL_STATUS_READY);
		$dbAuditTrail->setContext(VidiunAuditTrailContext::CLIENT);
		
		$enabled = vAuditTrailManager::traceEnabled($this->getPartnerId(), $dbAuditTrail);
		if(!$enabled)
			throw new VidiunAPIException(AuditTrailErrors::AUDIT_TRAIL_DISABLED, $this->getPartnerId(), $dbAuditTrail->getObjectType(), $dbAuditTrail->getAction());
			
		$created = $dbAuditTrail->save();
		if(!$created)
			return null;
		
		$auditTrail = new VidiunAuditTrail();
		$auditTrail->fromObject($dbAuditTrail, $this->getResponseProfile());
		
		return $auditTrail;
	}
	
	/**
	 * Retrieve an audit trail object by id
	 * 
	 * @action get
	 * @param int $id 
	 * @return VidiunAuditTrail
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function getAction($id)
	{
		$dbAuditTrail = AuditTrailPeer::retrieveByPK( $id );
		
		if(!$dbAuditTrail)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
			
		$auditTrail = new VidiunAuditTrail();
		$auditTrail->fromObject($dbAuditTrail, $this->getResponseProfile());
		
		return $auditTrail;
	}

		/**
	 * List audit trail objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunAuditTrailFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunAuditTrailListResponse
	 */
	function listAction(VidiunAuditTrailFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunAuditTrailFilter;
			
		if (!$pager)
			$pager = new VidiunFilterPager();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
}
