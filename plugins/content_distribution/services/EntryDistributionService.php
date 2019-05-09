<?php
/**
 * Entry Distribution service
 *
 * @service entryDistribution
 * @package plugins.contentDistribution
 * @subpackage api.services
 */
class EntryDistributionService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('EntryDistribution');
		
		if(!ContentDistributionPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ContentDistributionPlugin::PLUGIN_NAME);
	}
	
	/**
	 * Add new Entry Distribution
	 * 
	 * @action add
	 * @param VidiunEntryDistribution $entryDistribution
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_ALREADY_EXISTS
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function addAction(VidiunEntryDistribution $entryDistribution)
	{
		$entryDistribution->validateForInsert();
					
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->distributionProfileId);
		if (!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $entryDistribution->distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $entryDistribution->distributionProfileId);
		
		$dbEntry = entryPeer::retrieveByPK($entryDistribution->entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryDistribution->entryId);
		
		$dbEntryDistribution = EntryDistributionPeer::retrieveByEntryAndProfileId($entryDistribution->entryId, $entryDistribution->distributionProfileId);
		if($dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_ALREADY_EXISTS, $entryDistribution->entryId, $entryDistribution->distributionProfileId);

		$dbEntryDistribution = vContentDistributionManager::addEntryDistribution($dbEntry, $dbDistributionProfile);
		$entryDistribution->toInsertableObject($dbEntryDistribution);
		$dbEntryDistribution->setPartnerId($this->getPartnerId());
		$dbEntryDistribution->save();
		
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}
	
	/**
	 * Get Entry Distribution by id
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
			
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}
	
	/**
	 * Validates Entry Distribution by id for submission
	 * 
	 * @action validate
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 */
	function validateAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
			
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		$dbEntry = entryPeer::retrieveByPK($dbEntryDistribution->getEntryId());
		if($dbEntry)
		{
			vContentDistributionManager::assignFlavorAssets($dbEntryDistribution, $dbEntry, $dbDistributionProfile);
			vContentDistributionManager::assignThumbAssets($dbEntryDistribution, $dbEntry, $dbDistributionProfile);
			vContentDistributionManager::assignAssets($dbEntryDistribution, $dbEntry, $dbDistributionProfile);
		}
		
		$validationErrors = $dbDistributionProfile->validateForSubmission($dbEntryDistribution, DistributionAction::SUBMIT);
		$dbEntryDistribution->setValidationErrorsArray($validationErrors);
		$dbEntryDistribution->save();

		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}
	
	/**
	 * Update Entry Distribution by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunEntryDistribution $entryDistribution
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 */
	function updateAction($id, VidiunEntryDistribution $entryDistribution)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$entryDistribution->toUpdatableObject($dbEntryDistribution);
		$dbEntryDistribution->save();
		
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}
	
	/**
	 * Delete Entry Distribution by id
	 * 
	 * @action delete
	 * @param int $id
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);

		$dbEntryDistribution->setStatus(EntryDistributionStatus::DELETED);
		$dbEntryDistribution->save();
	}
	
	
	/**
	 * List all distribution providers
	 * 
	 * @action list
	 * @param VidiunEntryDistributionFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEntryDistributionListResponse
	 */
	function listAction(VidiunEntryDistributionFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunEntryDistributionFilter();
			
		if (! $pager)
			$pager = new VidiunFilterPager ();
			
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * Submits Entry Distribution to the remote destination
	 * 
	 * @action submitAdd
	 * @param int $id
	 * @param bool $submitWhenReady
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS
	 */
	function submitAddAction($id, $submitWhenReady = false)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$validStatus = array(
			EntryDistributionStatus::ERROR_DELETING,
			EntryDistributionStatus::ERROR_SUBMITTING,
			EntryDistributionStatus::ERROR_UPDATING,
			EntryDistributionStatus::PENDING,
			EntryDistributionStatus::QUEUED,
			EntryDistributionStatus::READY,
			EntryDistributionStatus::REMOVED,
		);
		
		if(!in_array($dbEntryDistribution->getStatus(), $validStatus))
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS, $id, $dbEntryDistribution->getStatus());
		
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED || $dbDistributionProfile->getSubmitEnabled() == DistributionProfileActionStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		vContentDistributionManager::submitAddEntryDistribution($dbEntryDistribution, $dbDistributionProfile, $submitWhenReady);

		$dbEntryDistribution->reload();
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}

	
	/**
	 * Submits Entry Distribution changes to the remote destination
	 * 
	 * @action submitUpdate
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS
	 */
	function submitUpdateAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$validStatus = array(
			EntryDistributionStatus::ERROR_DELETING,
			EntryDistributionStatus::ERROR_UPDATING,
			EntryDistributionStatus::READY,
		);
		
		if(!in_array($dbEntryDistribution->getStatus(), $validStatus))
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS, $id, $dbEntryDistribution->getStatus());
		
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED || $dbDistributionProfile->getUpdateEnabled() == DistributionProfileActionStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		vContentDistributionManager::submitUpdateEntryDistribution($dbEntryDistribution, $dbDistributionProfile);

		$dbEntryDistribution->reload();
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}

	
	/**
	 * Submits Entry Distribution report request
	 * 
	 * @action submitFetchReport
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS
	 */
	function submitFetchReportAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$validStatus = array(
			EntryDistributionStatus::READY,
		);
		
		if(!in_array($dbEntryDistribution->getStatus(), $validStatus))
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS, $id, $dbEntryDistribution->getStatus());
		
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED || $dbDistributionProfile->getReportEnabled() == DistributionProfileActionStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		vContentDistributionManager::submitFetchEntryDistributionReport($dbEntryDistribution, $dbDistributionProfile);

		$dbEntryDistribution->reload();
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}

	
	/**
	 * Deletes Entry Distribution from the remote destination
	 * 
	 * @action submitDelete
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS
	 */
	function submitDeleteAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$validStatus = array(
			EntryDistributionStatus::ERROR_DELETING,
			EntryDistributionStatus::ERROR_UPDATING,
			EntryDistributionStatus::READY,
		);
		
		if(!in_array($dbEntryDistribution->getStatus(), $validStatus))
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_STATUS, $id, $dbEntryDistribution->getStatus());
		
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED || $dbDistributionProfile->getDeleteEnabled() == DistributionProfileActionStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		vContentDistributionManager::submitDeleteEntryDistribution($dbEntryDistribution, $dbDistributionProfile);

		$dbEntryDistribution->reload();
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}

	
	/**
	 * Retries last submit action
	 * 
	 * @action retrySubmit
	 * @param int $id
	 * @return VidiunEntryDistribution
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND
	 * @throws ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED
	 */
	function retrySubmitAction($id)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$distributionProfileId = $dbEntryDistribution->getDistributionProfileId();
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		if(!$dbDistributionProfile)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_NOT_FOUND, $distributionProfileId);
		if ($dbDistributionProfile->getStatus() == DistributionProfileStatus::DISABLED)
			throw new VidiunAPIException(ContentDistributionErrors::DISTRIBUTION_PROFILE_DISABLED, $distributionProfileId);
		
		switch($dbEntryDistribution->getStatus())
		{
			case EntryDistributionStatus::QUEUED:
			case EntryDistributionStatus::SUBMITTING: 
			case EntryDistributionStatus::ERROR_SUBMITTING:
				vContentDistributionManager::submitAddEntryDistribution($dbEntryDistribution, $dbDistributionProfile, false);
				$dbEntryDistribution->reload();
				break;
				
			case EntryDistributionStatus::UPDATING:
			case EntryDistributionStatus::ERROR_UPDATING:
				vContentDistributionManager::submitUpdateEntryDistribution($dbEntryDistribution, $dbDistributionProfile);
				$dbEntryDistribution->reload();
				break;
				
			case EntryDistributionStatus::DELETING:
			case EntryDistributionStatus::ERROR_DELETING:
				vContentDistributionManager::submitDeleteEntryDistribution($dbEntryDistribution, $dbDistributionProfile);
				$dbEntryDistribution->reload();
				break;
				
			case EntryDistributionStatus::PENDING:
			case EntryDistributionStatus::READY:
			case EntryDistributionStatus::DELETED:
				break;
		}

		$entryDistribution = new VidiunEntryDistribution();
		$entryDistribution->fromObject($dbEntryDistribution, $this->getResponseProfile());
		return $entryDistribution;
	}

	/**
	 * Serves entry distribution sent data
	 *  
	 * @action serveSentData
	 * @param int $id
	 * @param VidiunDistributionAction $actionType
	 * @return file
	 *  
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_MISSING_LOG
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveSentDataAction($id, $actionType)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$fileName = "{$id}_{$actionType}_sent.xml";
		$fileSubType = null;
		switch($actionType)
		{
			case VidiunDistributionAction::SUBMIT:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_SUBMIT_DATA;
				break;
			case VidiunDistributionAction::UPDATE:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_UPDATE_DATA;
				break;
			case VidiunDistributionAction::DELETE:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_DELETE_DATA;
				break;
		}
		if(!$fileSubType)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_MISSING_LOG, $id);
		
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		return $this->serveFile($dbEntryDistribution, $fileSubType, $fileName, $dbEntryDistribution->getEntryId());
	}

	/**
	 * Serves entry distribution returned data
	 *  
	 * @action serveReturnedData
	 * @param int $id
	 * @param VidiunDistributionAction $actionType
	 * @return file
	 *  
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND
	 * @throws ContentDistributionErrors::ENTRY_DISTRIBUTION_MISSING_LOG
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveReturnedDataAction($id, $actionType)
	{
		$dbEntryDistribution = EntryDistributionPeer::retrieveByPK($id);
		if (!$dbEntryDistribution)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_NOT_FOUND, $id);
		
		$fileName = "{$id}_{$actionType}_return.xml";
		$fileSubType = null;
		switch($actionType)
		{
			case VidiunDistributionAction::SUBMIT:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_SUBMIT_RESULTS;
				break;
			case VidiunDistributionAction::UPDATE:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_UPDATE_RESULTS;
				break;
			case VidiunDistributionAction::DELETE:
				$fileSubType = EntryDistribution::FILE_SYNC_ENTRY_DISTRIBUTION_DELETE_RESULTS;
				break;
		}
		if(!$fileSubType)
			throw new VidiunAPIException(ContentDistributionErrors::ENTRY_DISTRIBUTION_MISSING_LOG, $id);
		
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		return $this->serveFile($dbEntryDistribution, $fileSubType, $fileName, $dbEntryDistribution->getEntryId());
	}
}
