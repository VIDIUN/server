<?php

/**
 * Schedule task service lets you create and manage scheduled task profiles
 *
 * @service scheduledTaskProfile
 * @package plugins.scheduledTask
 * @subpackage api.services
 */
class ScheduledTaskProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$partnerId = $this->getPartnerId();
		if (!ScheduledTaskPlugin::isAllowedPartner($partnerId))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, "{$this->serviceName}->{$this->actionName}");

		$this->applyPartnerFilterForClass('ScheduledTaskProfile');
	}

	/**
	 * Add a new scheduled task profile
	 *
	 * @action add
	 * @param VidiunScheduledTaskProfile $scheduledTaskProfile
	 * @return VidiunScheduledTaskProfile
	 *
	 * @disableRelativeTime $scheduledTaskProfile
	 */
	public function addAction(VidiunScheduledTaskProfile $scheduledTaskProfile)
	{
		/* @var $dbScheduledTaskProfile ScheduledTaskProfile */
		$dbScheduledTaskProfile = $scheduledTaskProfile->toInsertableObject();
		$dbScheduledTaskProfile->setPartnerId(vCurrentContext::getCurrentPartnerId());
		$dbScheduledTaskProfile->save();

		// return the saved object
		$scheduledTaskProfile = new VidiunScheduledTaskProfile();
		$scheduledTaskProfile->fromObject($dbScheduledTaskProfile, $this->getResponseProfile());
		return $scheduledTaskProfile;
	}

	/**
	 * Retrieve a scheduled task profile by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunScheduledTaskProfile
	 *
	 * @throws VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND
	 */
	public function getAction($id)
	{
		// get the object
		$dbScheduledTaskProfile = ScheduledTaskProfilePeer::retrieveByPK($id);
		if (!$dbScheduledTaskProfile)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND, $id);

		// return the found object
		$scheduledTaskProfile = new VidiunScheduledTaskProfile();
		$scheduledTaskProfile->fromObject($dbScheduledTaskProfile, $this->getResponseProfile());
		return $scheduledTaskProfile;
	}

	/**
	 * Update an existing scheduled task profile
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunScheduledTaskProfile $scheduledTaskProfile
	 * @return VidiunScheduledTaskProfile
	 *
	 * @throws VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND
	 * @disableRelativeTime $scheduledTaskProfile
	 */
	public function updateAction($id, VidiunScheduledTaskProfile $scheduledTaskProfile)
	{
		// get the object
		$dbScheduledTaskProfile = ScheduledTaskProfilePeer::retrieveByPK($id);
		if (!$dbScheduledTaskProfile)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND, $id);

		// save the object
		/** @var ScheduledTaskProfile $dbScheduledTaskProfile */
		$dbScheduledTaskProfile = $scheduledTaskProfile->toUpdatableObject($dbScheduledTaskProfile);
		$dbScheduledTaskProfile->save();

		// return the saved object
		$scheduledTaskProfile = new VidiunScheduledTaskProfile();
		$scheduledTaskProfile->fromObject($dbScheduledTaskProfile, $this->getResponseProfile());
		return $scheduledTaskProfile;
	}

	/**
	 * Delete a scheduled task profile
	 *
	 * @action delete
	 * @param int $id
	 *
	 * @throws VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND
	 */
	public function deleteAction($id)
	{
		// get the object
		$dbScheduledTaskProfile = ScheduledTaskProfilePeer::retrieveByPK($id);
		if (!$dbScheduledTaskProfile)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND, $id);

		// set the object status to deleted
		$dbScheduledTaskProfile->setStatus(ScheduledTaskProfileStatus::DELETED);
		$dbScheduledTaskProfile->save();
	}

	/**
	 * List scheduled task profiles
	 *
	 * @action list
	 * @param VidiunScheduledTaskProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunScheduledTaskProfileListResponse
	 */
	public function listAction(VidiunScheduledTaskProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunScheduledTaskProfileFilter();

		if (!$pager)
			$pager = new VidiunFilterPager();

		$scheduledTaskFilter = new ScheduledTaskProfileFilter();
		$filter->toObject($scheduledTaskFilter);

		$c = new Criteria();
		$scheduledTaskFilter->attachToCriteria($c);
		$count = ScheduledTaskProfilePeer::doCount($c);

		$pager->attachToCriteria($c);
		$list = ScheduledTaskProfilePeer::doSelect($c);

		$response = new VidiunScheduledTaskProfileListResponse();
		$response->objects = VidiunScheduledTaskProfileArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;

		return $response;
	}

	/**
	 * @action requestDryRun
	 * @param int $scheduledTaskProfileId
	 * @param int $maxResults
	 * @return int
	 * @throws VidiunAPIException
	 */
	public function requestDryRunAction($scheduledTaskProfileId, $maxResults = 500)
	{
		// get the object
		$dbScheduledTaskProfile = ScheduledTaskProfilePeer::retrieveByPK($scheduledTaskProfileId);
		if (!$dbScheduledTaskProfile)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::SCHEDULED_TASK_PROFILE_NOT_FOUND, $scheduledTaskProfileId);

		if (!in_array($dbScheduledTaskProfile->getStatus(), array(VidiunScheduledTaskProfileStatus::ACTIVE, VidiunScheduledTaskProfileStatus::DRY_RUN_ONLY)))
			throw new VidiunAPIException(VidiunScheduledTaskErrors::SCHEDULED_TASK_DRY_RUN_NOT_ALLOWED, $scheduledTaskProfileId);

		$jobData = new vScheduledTaskJobData();
		$jobData->setMaxResults($maxResults);
		$referenceTime = vCurrentContext::$vs_object->getPrivilegeValue(vs::PRIVILEGE_REFERENCE_TIME);
		if ($referenceTime)
			$jobData->setReferenceTime($referenceTime);

		$batchJob = $this->createScheduledTaskJob($dbScheduledTaskProfile, $jobData);
		return $batchJob->getId();
	}

	/**
	 * @action getDryRunResults
	 * @param int $requestId
	 * @return VidiunObjectListResponse
	 * @throws VidiunAPIException
	 */
	public function getDryRunResultsAction($requestId)
	{
		$batchJob = $this->getScheduledTaskBatchJob($requestId);
		/* @var $jobData vScheduledTaskJobData */
		$jobData = $batchJob->getData();
		$syncKey = $batchJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD);
		if($jobData->getFileFormat() == DryRunFileType::CSV)
		{
			throw new VidiunAPIException(VidiunScheduledTaskErrors::DRY_RUN_RESULT_IS_TOO_BIG.
				$this->getDryRunResultUrl($batchJob->getPartnerId(), $requestId));
		}

		$data = vFileSyncUtils::file_get_contents($syncKey, true);
		return unserialize($data);
	}
	
	/**
	 * Serves dry run results by its request id
	 * @action serveDryRunResults
	 * @param int $requestId
	 * @return file
	 * @throws VidiunAPIException
	 */
	public function serveDryRunResultsAction($requestId)
	{
		$vs = $this->getVs();
		if(!$vs || !($vs->isAdmin() || $vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, $requestId)))
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);

		$fileName = $requestId."csv";
		$batchJob = $this->getScheduledTaskBatchJob($requestId);
		return $this->serveFile($batchJob, BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD, $fileName);
	}

	/**
	 * Get a url to serve dry run result action
	 * @param int $requestId
	 * @return string
	 */
	private function getDryRunResultUrl($partnerId, $requestId)
	{
		$finalPath ='/api_v3/service/scheduledtask_scheduledtaskprofile/action/serveDryRunResults/requestId/';
		$finalPath .="$requestId";
		$vsStr = $this->getPartnerVs($partnerId, $requestId);
		$finalPath .= "/vs/".$vsStr;
		$downloadUrl = myPartnerUtils::getCdnHost($partnerId) . $finalPath;

		return $downloadUrl;
	}

	private function getPartnerVs($partnerId, $requestId)
	{
		$vsStr = "";
		$partner = PartnerPeer::retrieveByPK ( $partnerId );
		$privilege = vs::PRIVILEGE_DOWNLOAD . ":" . $requestId;
		$maxExpiry = 86400;
		$expiry = $partner->getVsMaxExpiryInSeconds();
		if(!$expiry || $expiry > $maxExpiry)
			$expiry = $maxExpiry;

		$result = vSessionUtils::startVSession ( $partnerId, $partner->getSecret (), null, $vsStr, $expiry, false, "", $privilege );

		if ($result < 0)
			throw new Exception ( "Failed to generate session for asset [" . $this->getId () . "] of type " . $this->getType () );

		return $vsStr;
	}

	/**
	 * @action getDryRunResults
	 * @param int $requestId
	 * @return BatchJob
	 * @throws VidiunAPIException
	 */
	private function getScheduledTaskBatchJob($requestId)
	{
		$this->applyPartnerFilterForClass('BatchJob');
		$batchJob = BatchJobPeer::retrieveByPK($requestId);
		$batchJobType = ScheduledTaskPlugin::getBatchJobTypeCoreValue(ScheduledTaskBatchType::SCHEDULED_TASK);
		if (is_null($batchJob) || $batchJob->getJobType() != $batchJobType)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::OBJECT_NOT_FOUND);

		if (in_array($batchJob->getStatus(), array(VidiunBatchJobStatus::FAILED, VidiunBatchJobStatus::FATAL)))
			throw new VidiunAPIException(VidiunScheduledTaskErrors::DRY_RUN_FAILED);

		if ($batchJob->getStatus() != VidiunBatchJobStatus::FINISHED)
			throw new VidiunAPIException(VidiunScheduledTaskErrors::DRY_RUN_NOT_READY);

		return $batchJob;
	}

	/**
	 * @param ScheduledTaskProfile $scheduledTaskProfile
	 * @param vScheduledTaskJobData $jobData
	 * @return BatchJob
	 */
	protected function createScheduledTaskJob(ScheduledTaskProfile $scheduledTaskProfile, vScheduledTaskJobData $jobData)
	{
		$scheduledTaskProfileId = $scheduledTaskProfile->getId();
		$jobType = ScheduledTaskPlugin::getBatchJobTypeCoreValue(ScheduledTaskBatchType::SCHEDULED_TASK);
		$objectType = ScheduledTaskPlugin::getBatchJobObjectTypeCoreValue(ScheduledTaskBatchJobObjectType::SCHEDULED_TASK_PROFILE);
		VidiunLog::log("Creating scheduled task dry run job for profile [".$scheduledTaskProfileId."]");
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($scheduledTaskProfile->getPartnerId());
		$batchJob->setObjectId($scheduledTaskProfileId);
		$batchJob->setObjectType($objectType);
		$batchJob->setStatus(BatchJob::BATCHJOB_STATUS_PENDING);
		$batchJob = vJobsManager::addJob($batchJob, $jobData, $jobType);

		return $batchJob;
	}
}
