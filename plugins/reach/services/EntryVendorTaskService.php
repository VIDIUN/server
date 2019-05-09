<?php

/**
 * Entry Vendor Task Service
 *
 * @service entryVendorTask
 * @package plugins.reach
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */
class EntryVendorTaskService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if (!ReachPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ReachPlugin::PLUGIN_NAME);
		
		if (!in_array($actionName, array('getJobs', 'updateJob', 'list', 'extendAccessKey')))
		{
			$this->applyPartnerFilterForClass('entryVendorTask');
			$this->applyPartnerFilterForClass('reachProfile');
		}
	}
	
	/**
	 * Allows you to add a entry vendor task
	 *
	 * @action add
	 * @param VidiunEntryVendorTask $entryVendorTask
	 * @return VidiunEntryVendorTask
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 * @throws VidiunReachErrors::CATALOG_ITEM_NOT_FOUND
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_DUPLICATION
	 * @throws VidiunReachErrors::EXCEEDED_MAX_CREDIT_ALLOWED
	 */
	public function addAction(VidiunEntryVendorTask $entryVendorTask)
	{
		$entryVendorTask->validateForInsert();
		
		$dbEntry = entryPeer::retrieveByPK($entryVendorTask->entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryVendorTask->entryId);
		
		$dbReachProfile = ReachProfilePeer::retrieveActiveByPk($entryVendorTask->reachProfileId);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::REACH_PROFILE_NOT_FOUND, $entryVendorTask->reachProfileId);
		
		$dbVendorCatalogItem = VendorCatalogItemPeer::retrieveByPK($entryVendorTask->catalogItemId);
		if (!$dbVendorCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $entryVendorTask->catalogItemId);
		
		$partnerCatalogItem = PartnerCatalogItemPeer::retrieveByCatalogItemId($entryVendorTask->catalogItemId, vCurrentContext::getCurrentPartnerId());
		if (!$partnerCatalogItem)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_ENABLED_FOR_ACCOUNT, $entryVendorTask->catalogItemId);
		
		$taskVersion = $dbVendorCatalogItem->getTaskVersion($dbEntry->getId(), $entryVendorTask->taskJobData ? $entryVendorTask->taskJobData->toObject() : null);
		if (vReachUtils::isDuplicateTask($entryVendorTask->entryId, $entryVendorTask->catalogItemId, vCurrentContext::getCurrentPartnerId(), $taskVersion))
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_DUPLICATION, $entryVendorTask->entryId, $entryVendorTask->catalogItemId, $taskVersion);
		
		//check if credit has expired
		if (vReachUtils::hasCreditExpired($dbReachProfile))
			throw new VidiunAPIException(VidiunReachErrors::CREDIT_EXPIRED, $entryVendorTask->entryId, $entryVendorTask->catalogItemId);
		
		if (!vReachUtils::isEnoughCreditLeft($dbEntry, $dbVendorCatalogItem, $dbReachProfile))
			throw new VidiunAPIException(VidiunReachErrors::EXCEEDED_MAX_CREDIT_ALLOWED, $entryVendorTask->entryId, $entryVendorTask->catalogItemId);
		
		$dbEntryVendorTask = kReachManager::addEntryVendorTask($dbEntry, $dbReachProfile, $dbVendorCatalogItem, !kCurrentContext::$is_admin_session, $taskVersion);
		if(!$dbEntryVendorTask)
		{
			throw new KalturaAPIException(KalturaReachErrors::TASK_NOT_CREATED, $entryVendorTask->entryId, $entryVendorTask->catalogItemId);
		}
		$entryVendorTask->toInsertableObject($dbEntryVendorTask);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * Retrieve specific entry vendor task by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunEntryVendorTask
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * List VidiunEntryVendorTask objects
	 *
	 * @action list
	 * @param VidiunEntryVendorTaskFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEntryVendorTaskListResponse
	 */
	public function listAction(VidiunEntryVendorTaskFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunEntryVendorTaskFilter();
		
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		if (!PermissionPeer::isValidForPartner(PermissionName::REACH_VENDOR_PARTNER_PERMISSION, vCurrentContext::getCurrentPartnerId()))
			$this->applyPartnerFilterForClass('entryVendorTask');
		else
			$filter->vendorPartnerIdEqual = vCurrentContext::getCurrentPartnerId();
		
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Update entry vendor task. Only the properties that were set will be updated.
	 *
	 * @action update
	 * @param int $id vendor task id to update
	 * @param VidiunEntryVendorTask $entryVendorTask evntry vendor task to update
	 *
	 * @return VidiunEntryVendorTask
	 *
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 */
	public function updateAction($id, VidiunEntryVendorTask $entryVendorTask)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$dbEntry = entryPeer::retrieveByPK($dbEntryVendorTask->getEntryId());
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbEntryVendorTask->getEntryId());
		
		$dbEntryVendorTask = $entryVendorTask->toUpdatableObject($dbEntryVendorTask);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * Approve entry vendor task for execution.
	 *
	 * @action approve
	 * @param int $id vendor task id to approve
	 * @param VidiunEntryVendorTask $entryVendorTask evntry vendor task to approve
	 *
	 * @return VidiunEntryVendorTask
	 *
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 * @throws VidiunReachErrors::CANNOT_APPROVE_NOT_MODERATED_TASK
	 * @throws VidiunReachErrors::EXCEEDED_MAX_CREDIT_ALLOWED
	 */
	public function approveAction($id)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$dbEntry = entryPeer::retrieveByPK($dbEntryVendorTask->getEntryId());
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbEntryVendorTask->getEntryId());
		
		if ($dbEntryVendorTask->getStatus() != EntryVendorTaskStatus::PENDING_MODERATION)
			throw new VidiunAPIException(VidiunReachErrors::CANNOT_APPROVE_NOT_MODERATED_TASK);
		
		if (!vReachUtils::checkCreditForApproval($dbEntryVendorTask))
			throw new VidiunAPIException(VidiunReachErrors::EXCEEDED_MAX_CREDIT_ALLOWED, $dbEntryVendorTask->getEntry(), $dbEntryVendorTask->getCatalogItem());
		
		$dbEntryVendorTask->setModeratingUser($this->getVuser()->getPuserId());
		$dbEntryVendorTask->setStatus(VidiunEntryVendorTaskStatus::PENDING);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * Reject entry vendor task for execution.
	 *
	 * @action reject
	 * @param int $id vendor task id to reject
	 * @param string $rejectReason
	 * @param VidiunEntryVendorTask $entryVendorTask evntry vendor task to reject
	 *
	 * @return VidiunEntryVendorTask
	 *
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 * @throws VidiunReachErrors::CANNOT_REJECT_NOT_MODERATED_TASK
	 */
	public function rejectAction($id,  $rejectReason = null)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$dbEntry = entryPeer::retrieveByPK($dbEntryVendorTask->getEntryId());
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbEntryVendorTask->getEntryId());
		
		if ($dbEntryVendorTask->getStatus() != EntryVendorTaskStatus::PENDING_MODERATION)
			throw new VidiunAPIException(VidiunReachErrors::CANNOT_REJECT_NOT_MODERATED_TASK);
		
		$dbEntryVendorTask->setModeratingUser($this->getVuser()->getPuserId());
		$dbEntryVendorTask->setStatus(VidiunEntryVendorTaskStatus::REJECTED);
		$dbEntryVendorTask->setErrDescription($rejectReason);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * get VidiunEntryVendorTask objects for specific vendor partner
	 *
	 * @action getJobs
	 * @param VidiunEntryVendorTaskFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEntryVendorTaskListResponse
	 */
	public function getJobsAction(VidiunEntryVendorTaskFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!PermissionPeer::isValidForPartner(PermissionName::REACH_VENDOR_PARTNER_PERMISSION, vCurrentContext::$vs_partner_id))
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_SERVICE_GET_JOB_NOT_ALLOWED, vCurrentContext::getCurrentPartnerId());
		
		if (!$filter)
			$filter = new VidiunEntryVendorTaskFilter();
		
		$filter->vendorPartnerIdEqual = vCurrentContext::getCurrentPartnerId();
		$filter->statusEqual = EntryVendorTaskStatus::PENDING;
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Update entry vendor task. Only the properties that were set will be updated.
	 *
	 * @action updateJob
	 * @param int $id vendor task id to update
	 * @param VidiunEntryVendorTask $entryVendorTask evntry vendor task to update
	 * @return VidiunEntryVendorTask
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 */
	public function updateJobAction($id, VidiunEntryVendorTask $entryVendorTask)
	{
		if (!PermissionPeer::isValidForPartner(PermissionName::REACH_VENDOR_PARTNER_PERMISSION, vCurrentContext::$vs_partner_id))
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_SERVICE_GET_JOB_NOT_ALLOWED, vCurrentContext::getCurrentPartnerId());
		
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPKAndVendorPartnerId($id, vCurrentContext::$vs_partner_id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$dbEntryVendorTask = $entryVendorTask->toUpdatableObject($dbEntryVendorTask);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * Cancel entry task. will only occur for task in PENDING or PENDING_MODERATION status
	 *
	 * @action abort
	 * @param int $id vendor task id
	 * @param string $abortReason
	 * @return VidiunEntryVendorTask
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 */
	public function abortAction($id, $abortReason = null)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		
		$dbEntry = entryPeer::retrieveByPK($dbEntryVendorTask->getEntryId());
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $dbEntryVendorTask->getEntryId());
		
		/* @var EntryVendorTask $dbEntryVendorTask */
		if ($dbEntryVendorTask->getStatus() != EntryVendorTaskStatus::PENDING_MODERATION)
			throw new VidiunAPIException(VidiunReachErrors::CANNOT_ABORT_NOT_MODERATED_TASK, $id);
		
		if (!vCurrentContext::$is_admin_session && vCurrentContext::$vs_uid != $dbEntryVendorTask->getUserId())
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_ACTION_NOT_ALLOWED, $id, vCurrentContext::$vs_uid);
		
		$dbEntryVendorTask->setStatus(VidiunEntryVendorTaskStatus::ABORTED);
		$dbEntryVendorTask->setErrDescription($abortReason);
		$dbEntryVendorTask->save();
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
	
	/**
	 * add batch job that sends an email with a link to download an updated CSV that contains list of users
	 *
	 * @action exportToCsv
	 * @param VidiunEntryVendorTaskFilter $filter A filter used to exclude specific tasks
	 * @return string
	 */
	function exportToCsvAction(VidiunEntryVendorTaskFilter $filter)
	{
		if (!$filter)
			$filter = new VidiunEntryVendorTaskFilter();
		$dbFilter = new EntryVendorTaskFilter();
		$filter->toObject($dbFilter);
		
		$vuser = $this->getVuser();
		if (!$vuser || !$vuser->getEmail())
			throw new VidiunAPIException(APIErrors::USER_EMAIL_NOT_FOUND, $vuser);
		
		$jobData = new vEntryVendorTaskCsvJobData();
		$jobData->setFilter($dbFilter);
		$jobData->setUserMail($vuser->getEmail());
		$jobData->setUserName($vuser->getPuserId());
		
		vJobsManager::addExportCsvJob($jobData, $this->getPartnerId(), ReachPlugin::getExportTypeCoreValue(EntryVendorTaskExportObjectType::ENTRY_VENDOR_TASK));
		
		return $vuser->getEmail();
	}
	
	
	/**
	 *
	 * Will serve a requested csv
	 * @action serveCsv
	 *
	 * @deprecated use exportCsv.serveCsv
	 * @param string $id - the requested file id
	 * @return string
	 */
	public function serveCsvAction($id)
	{
		$file_path = ExportCsvService::generateCsvPath($id, $this->getVs());
		
		return $this->dumpFile($file_path, 'text/csv');
	}

	/**
	 * Extend access key in case the existing one has expired.
	 *
	 * @action extendAccessKey
	 * @param int $id vendor task id
	 * @return VidiunEntryVendorTask
	 *
	 * @throws VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND
	 * @throws VidiunReachErrors::CANNOT_EXTEND_ACCESS_KEY
	 */
	public function extendAccessKeyAction($id)
	{
		$dbEntryVendorTask = EntryVendorTaskPeer::retrieveByPK($id);
		if (!$dbEntryVendorTask)
		{
			throw new VidiunAPIException(VidiunReachErrors::ENTRY_VENDOR_TASK_NOT_FOUND, $id);
		}
		
		if($dbEntryVendorTask->getStatus() != EntryVendorTaskStatus::PROCESSING)
		{
			throw new VidiunAPIException(VidiunReachErrors::CANNOT_EXTEND_ACCESS_KEY);
		}
		
		$shouldModerateOutput = $dbEntryVendorTask->getIsOutputModerated();
		$accessKeyExpiry = $dbEntryVendorTask->getAccessKeyExpiry();
		
		try
		{
			$dbEntryVendorTask->setAccessKey(kReachUtils::generateReachVendorKs($dbEntryVendorTask->getEntryId(), $shouldModerateOutput, $accessKeyExpiry, true));
			$dbEntryVendorTask->save();
		}
		catch (Exception $e)
		{
			throw new VidiunAPIException(VidiunReachErrors::FAILED_EXTEND_ACCESS_KEY);
		}
		
		// return the saved object
		$entryVendorTask = new VidiunEntryVendorTask();
		$entryVendorTask->fromObject($dbEntryVendorTask, $this->getResponseProfile());
		return $entryVendorTask;
	}
}
