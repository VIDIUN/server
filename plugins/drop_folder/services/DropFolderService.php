<?php

/**
 * DropFolder service lets you create and manage drop folders
 * @service dropFolder
 * @package plugins.dropFolder
 * @subpackage api.services
 */
class DropFolderService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if (!DropFolderPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, DropFolderPlugin::PLUGIN_NAME);
			
		$this->applyPartnerFilterForClass('DropFolder');
		$this->applyPartnerFilterForClass('DropFolderFile');
	}
		
	
	
	/**
	 * Allows you to add a new VidiunDropFolder object
	 * 
	 * @action add
	 * @param VidiunDropFolder $dropFolder
	 * @return VidiunDropFolder
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunErrors::INGESTION_PROFILE_ID_NOT_FOUND
	 * @throws VidiunDropFolderErrors::DROP_FOLDER_ALREADY_EXISTS
	 * @throws VidiunErrors::DATA_CENTER_ID_NOT_FOUND
	 */
	public function addAction(VidiunDropFolder $dropFolder)
	{
		// check for required parameters
		$dropFolder->validatePropertyNotNull('name');
		$dropFolder->validatePropertyNotNull('status');
		$dropFolder->validatePropertyNotNull('type');
		$dropFolder->validatePropertyNotNull('dc');
		$dropFolder->validatePropertyNotNull('path');
		$dropFolder->validatePropertyNotNull('partnerId');
		$dropFolder->validatePropertyMinValue('fileSizeCheckInterval', 0, true);
		$dropFolder->validatePropertyMinValue('autoFileDeleteDays', 0, true);
		$dropFolder->validatePropertyNotNull('fileHandlerType');
		$dropFolder->validatePropertyNotNull('fileHandlerConfig');
		
		// validate values
		
		if (is_null($dropFolder->fileSizeCheckInterval)) {
			$dropFolder->fileSizeCheckInterval = DropFolder::FILE_SIZE_CHECK_INTERVAL_DEFAULT_VALUE;
		}
		
		if (is_null($dropFolder->fileNamePatterns)) {
			$dropFolder->fileNamePatterns = DropFolder::FILE_NAME_PATTERNS_DEFAULT_VALUE;
		}
		
		if (!vDataCenterMgr::dcExists($dropFolder->dc)) {
			throw new VidiunAPIException(VidiunErrors::DATA_CENTER_ID_NOT_FOUND, $dropFolder->dc);
		}
		
		if (!PartnerPeer::retrieveByPK($dropFolder->partnerId)) {
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $dropFolder->partnerId);
		}
		
		if (!DropFolderPlugin::isAllowedPartner($dropFolder->partnerId))
		{
			throw new VidiunAPIException(VidiunErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER, DropFolderPlugin::getPluginName(), $dropFolder->partnerId);
		}

		if($dropFolder->type == VidiunDropFolderType::LOCAL)
		{
			$existingDropFolder = DropFolderPeer::retrieveByPathDefaultFilter($dropFolder->path);
			if ($existingDropFolder) {
				throw new VidiunAPIException(VidiunDropFolderErrors::DROP_FOLDER_ALREADY_EXISTS, $dropFolder->path);
			}
		}
		
		if (!is_null($dropFolder->conversionProfileId)) {
			$conversionProfileDb = conversionProfile2Peer::retrieveByPK($dropFolder->conversionProfileId);
			if (!$conversionProfileDb) {
				throw new VidiunAPIException(VidiunErrors::INGESTION_PROFILE_ID_NOT_FOUND, $dropFolder->conversionProfileId);
			}
		}
		
		// save in database
		$dbDropFolder = $dropFolder->toInsertableObject();
		$dbDropFolder->save();
		
		// return the saved object
		$dropFolder = VidiunDropFolder::getInstanceByType($dbDropFolder->getType());
		$dropFolder->fromObject($dbDropFolder, $this->getResponseProfile());
		return $dropFolder;
		
	}
	
	/**
	 * Retrieve a VidiunDropFolder object by ID
	 * 
	 * @action get
	 * @param int $dropFolderId 
	 * @return VidiunDropFolder
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($dropFolderId)
	{
		$dbDropFolder = DropFolderPeer::retrieveByPK($dropFolderId);
		
		if (!$dbDropFolder) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderId);
		}
			
		$dropFolder = VidiunDropFolder::getInstanceByType($dbDropFolder->getType());
		$dropFolder->fromObject($dbDropFolder, $this->getResponseProfile());
		
		return $dropFolder;
	}
	

	/**
	 * Update an existing VidiunDropFolder object
	 * 
	 * @action update
	 * @param int $dropFolderId
	 * @param VidiunDropFolder $dropFolder
	 * @return VidiunDropFolder
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunErrors::INGESTION_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::DATA_CENTER_ID_NOT_FOUND
	 */	
	public function updateAction($dropFolderId, VidiunDropFolder $dropFolder)
	{
		$dbDropFolder = DropFolderPeer::retrieveByPK($dropFolderId);
		
		if (!$dbDropFolder) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderId);
		}
		
		$dropFolder->validatePropertyMinValue('fileSizeCheckInterval', 0, true);
		$dropFolder->validatePropertyMinValue('autoFileDeleteDays', 0, true);
		
		if (!is_null($dropFolder->path) && $dropFolder->path != $dbDropFolder->getPath() && $dropFolder->type == VidiunDropFolderType::LOCAL) 
		{
			$existingDropFolder = DropFolderPeer::retrieveByPathDefaultFilter($dropFolder->path);
			if ($existingDropFolder) {
				throw new VidiunAPIException(VidiunDropFolderErrors::DROP_FOLDER_ALREADY_EXISTS, $dropFolder->path);
			}
		}
		
		if (!is_null($dropFolder->dc)) {
			if (!vDataCenterMgr::dcExists($dropFolder->dc)) {
				throw new VidiunAPIException(VidiunErrors::DATA_CENTER_ID_NOT_FOUND, $dropFolder->dc);
			}
		}
		
		if (!is_null($dropFolder->conversionProfileId)) {
			$conversionProfileDb = conversionProfile2Peer::retrieveByPK($dropFolder->conversionProfileId);
			if (!$conversionProfileDb) {
				throw new VidiunAPIException(VidiunErrors::INGESTION_PROFILE_ID_NOT_FOUND, $dropFolder->conversionProfileId);
			}
		}

		$dbDropFolder = $dropFolder->toUpdatableObject($dbDropFolder);
		$dbDropFolder->save();
	
		$dropFolder = VidiunDropFolder::getInstanceByType($dbDropFolder->getType());
		$dropFolder->fromObject($dbDropFolder, $this->getResponseProfile());
		
		return $dropFolder;
	}

	/**
	 * Mark the VidiunDropFolder object as deleted
	 * 
	 * @action delete
	 * @param int $dropFolderId 
	 * @return VidiunDropFolder
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($dropFolderId)
	{
		$dbDropFolder = DropFolderPeer::retrieveByPK($dropFolderId);
		
		if (!$dbDropFolder) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderId);
		}

		$dbDropFolder->setStatus(DropFolderStatus::DELETED);
		$dbDropFolder->save();
			
		$dropFolder = VidiunDropFolder::getInstanceByType($dbDropFolder->getType());
		$dropFolder->fromObject($dbDropFolder, $this->getResponseProfile());
		
		return $dropFolder;
	}
	
	/**
	 * List VidiunDropFolder objects
	 * 
	 * @action list
	 * @param VidiunDropFolderFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDropFolderListResponse
	 */
	public function listAction(VidiunDropFolderFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunDropFolderFilter();
			
		$dropFolderFilter = $filter->toObject();

		$c = new Criteria();
		$dropFolderFilter->attachToCriteria($c);
		$count = DropFolderPeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = DropFolderPeer::doSelect($c);
		
		$response = new VidiunDropFolderListResponse();
		$response->objects = VidiunDropFolderArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}

	/**
	 * getExclusive VidiunDropFolder object
	 *
	 * @action getExclusiveDropFolder
	 * @param string $tag
	 * @param int $maxTime
	 * @return VidiunDropFolder
	 */
	public function getExclusiveDropFolderAction($tag, $maxTime)
	{
		$allocateDropFolder = vDropFolderAllocator::getDropFolder($tag, $maxTime);
		if ($allocateDropFolder && self::isValidForWatch($allocateDropFolder))
		{
			$dropFolder = VidiunDropFolder::getInstanceByType($allocateDropFolder->getType());
			$dropFolder->fromObject($allocateDropFolder, $this->getResponseProfile());
			return $dropFolder;
		}
	}
 	
	/**
	 * freeExclusive VidiunDropFolder object
	 *
	 * @action freeExclusiveDropFolder
	 * @param int $dropFolderId
	 * @param string $errorCode
	 * @param string $errorDescription
	 * @throws VidiunAPIException
	 * @return VidiunDropFolder
	 */
	public function freeExclusiveDropFolderAction($dropFolderId, $errorCode = null, $errorDescription = null)
	{
		vDropFolderAllocator::freeDropFolder($dropFolderId);

		$dbDropFolder = DropFolderPeer::retrieveByPK($dropFolderId);
		if (!$dbDropFolder)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderId);

		$dbDropFolder->setLastAccessedAt(time());
		$dbDropFolder->setErrorCode($errorCode);
		$dbDropFolder->setErrorDescription($errorDescription);
		$dbDropFolder->save();

		$dropFolder = VidiunDropFolder::getInstanceByType($dbDropFolder->getType());
		$dropFolder->fromObject($dbDropFolder, $this->getResponseProfile());

		return $dropFolder;
	}

	private static function isValidForWatch(DropFolder $dropFolder)
	{
		$partner = PartnerPeer::retrieveByPK($dropFolder->getPartnerId());
		if (!$partner || $partner->getStatus() != Partner::PARTNER_STATUS_ACTIVE
			|| !$partner->getPluginEnabled(DropFolderPlugin::PLUGIN_NAME))
			return false;

		return true;
	}
	
}
