<?php

/**
 * DropFolderFile service lets you create and manage drop folder files
 * @service dropFolderFile
 * @package plugins.dropFolder
 * @subpackage api.services
 */
class DropFolderFileService extends VidiunBaseService
{
	const MYSQL_CODE_DUPLICATE_KEY = 23000;
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if (!DropFolderPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, DropFolderPlugin::PLUGIN_NAME);
		
		$this->applyPartnerFilterForClass('DropFolder');
		$this->applyPartnerFilterForClass('DropFolderFile');
	}
		
	/**
	 * Allows you to add a new VidiunDropFolderFile object
	 * 
	 * @action add
	 * @param VidiunDropFolderFile $dropFolderFile
	 * @return VidiunDropFolderFile
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws VidiunDropFolderErrors::DROP_FOLDER_NOT_FOUND
	 */
	public function addAction(VidiunDropFolderFile $dropFolderFile)
	{
		return $this->newFileAddedOrDetected($dropFolderFile, DropFolderFileStatus::UPLOADING);
	}
	
	/**
	 * Retrieve a VidiunDropFolderFile object by ID
	 * 
	 * @action get
	 * @param int $dropFolderFileId 
	 * @return VidiunDropFolderFile
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($dropFolderFileId)
	{
		$dbDropFolderFile = DropFolderFilePeer::retrieveByPK($dropFolderFileId);
		
		if (!$dbDropFolderFile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
		}
			
		$dropFolderFile = VidiunDropFolderFile::getInstanceByType($dbDropFolderFile->getType());
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		
		return $dropFolderFile;
	}
	

	/**
	 * Update an existing VidiunDropFolderFile object
	 * 
	 * @action update
	 * @param int $dropFolderFileId
	 * @param VidiunDropFolderFile $dropFolderFile
	 * @return VidiunDropFolderFile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	public function updateAction($dropFolderFileId, VidiunDropFolderFile $dropFolderFile)
	{
		$dbDropFolderFile = DropFolderFilePeer::retrieveByPK($dropFolderFileId);
		
		if (!$dbDropFolderFile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
		}
		
		if (!is_null($dropFolderFile->fileSize)) {
			$dropFolderFile->validatePropertyMinValue('fileSize', 0);
		}
					
		$dbDropFolderFile = $dropFolderFile->toUpdatableObject($dbDropFolderFile);
		$dbDropFolderFile->save();
	
		$dropFolderFile = new VidiunDropFolderFile();
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		
		return $dropFolderFile;
	}
	

	/**
	 * Update status of VidiunDropFolderFile
	 * 
	 * @action updateStatus
	 * @param int $dropFolderFileId
	 * @param VidiunDropFolderFileStatus $status
	 * @return VidiunDropFolderFile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	public function updateStatusAction($dropFolderFileId, $status)
	{
		$dbDropFolderFile = DropFolderFilePeer::retrieveByPK($dropFolderFileId);
		if (!$dbDropFolderFile)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
			
		if ($status != VidiunDropFolderFileStatus::PURGED && $dbDropFolderFile->getStatus() == VidiunDropFolderFileStatus::DELETED)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
		
		$dbDropFolderFile->setStatus($status);
		$dbDropFolderFile->save();
	
		$dropFolderFile = VidiunDropFolderFile::getInstanceByType($dbDropFolderFile->getType());
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		
		return $dropFolderFile;
	}

	/**
	 * Mark the VidiunDropFolderFile object as deleted
	 * 
	 * @action delete
	 * @param int $dropFolderFileId 
	 * @return VidiunDropFolderFile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($dropFolderFileId)
	{
		$dbDropFolderFile = DropFolderFilePeer::retrieveByPK($dropFolderFileId);

		if (!$dbDropFolderFile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
		}
		
		$dbDropFolderFile->setStatus(DropFolderFileStatus::DELETED);
		$dbDropFolderFile->save();
			
		$dropFolderFile = VidiunDropFolderFile::getInstanceByType($dbDropFolderFile->getType());
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		
		return $dropFolderFile;
	}
	
	/**
	 * List VidiunDropFolderFile objects
	 * 
	 * @action list
	 * @param VidiunDropFolderFileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunDropFolderFileListResponse
	 */
	public function listAction(VidiunDropFolderFileFilter  $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
		{
			$filter = new VidiunDropFolderFileFilter();
		}
		
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
		
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	
	/**
	 * Set the VidiunDropFolderFile status to ignore (VidiunDropFolderFileStatus::IGNORE)
	 * 
	 * @action ignore
	 * @param int $dropFolderFileId 
	 * @return VidiunDropFolderFile
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	public function ignoreAction($dropFolderFileId)
	{
		$dbDropFolderFile = DropFolderFilePeer::retrieveByPK($dropFolderFileId);
		
		if (!$dbDropFolderFile) {
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $dropFolderFileId);
		}

		$dbDropFolderFile->setStatus(DropFolderFileStatus::IGNORE);
		$dbDropFolderFile->save();
			
		$dropFolderFile = new VidiunDropFolderFile();
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		
		return $dropFolderFile;
	}
	
	private function newFileAddedOrDetected(VidiunDropFolderFile $dropFolderFile, $fileStatus)
	{
		// check for required parameters
		$dropFolderFile->validatePropertyNotNull('dropFolderId');
		$dropFolderFile->validatePropertyNotNull('fileName');
		$dropFolderFile->validatePropertyMinValue('fileSize', 0);
		
		// check that drop folder id exists in the system
		$dropFolder = DropFolderPeer::retrieveByPK($dropFolderFile->dropFolderId);
		if (!$dropFolder) {
			throw new VidiunAPIException(VidiunDropFolderErrors::DROP_FOLDER_NOT_FOUND, $dropFolderFile->dropFolderId);
		}
				
		// save in database
		$dropFolderFile->status = null;		
		$dbDropFolderFile = $dropFolderFile->toInsertableObject();
		/* @var $dbDropFolderFile DropFolderFile  */
		$dbDropFolderFile->setPartnerId($dropFolder->getPartnerId());
		$dbDropFolderFile->setStatus($fileStatus);
		$dbDropFolderFile->setType($dropFolder->getType());
		try 
		{
			$dbDropFolderFile->save();	
		}
		catch(PropelException $e)
		{
			if($e->getCause() && $e->getCause()->getCode() == self::MYSQL_CODE_DUPLICATE_KEY) //unique constraint
			{
				$existingDropFolderFile = DropFolderFilePeer::retrieveByDropFolderIdAndFileName($dropFolderFile->dropFolderId, $dropFolderFile->fileName);
				switch($existingDropFolderFile->getStatus())
				{					
					case DropFolderFileStatus::PARSED:
						VidiunLog::info('Exisiting file status is PARSED, updating status to ['.$fileStatus.']');
						$existingDropFolderFile = $dropFolderFile->toUpdatableObject($existingDropFolderFile);
						$existingDropFolderFile->setStatus($fileStatus);						
						$existingDropFolderFile->save();
						$dbDropFolderFile = $existingDropFolderFile;
						break;
					case DropFolderFileStatus::DETECTED:
						VidiunLog::info('Exisiting file status is DETECTED, updating status to ['.$fileStatus.']');
						$existingDropFolderFile = $dropFolderFile->toUpdatableObject($existingDropFolderFile);
						if($existingDropFolderFile->getStatus() != $fileStatus)
							$existingDropFolderFile->setStatus($fileStatus);
						$existingDropFolderFile->save();
						$dbDropFolderFile = $existingDropFolderFile;
						break;
					case DropFolderFileStatus::UPLOADING:
						if($fileStatus == DropFolderFileStatus::UPLOADING)
						{
							VidiunLog::log('Exisiting file status is UPLOADING, updating properties');
							$existingDropFolderFile = $dropFolderFile->toUpdatableObject($existingDropFolderFile);
							$existingDropFolderFile->save();
							$dbDropFolderFile = $existingDropFolderFile;
							break;							
						}
					default:
						VidiunLog::log('Setting current file to PURGED ['.$existingDropFolderFile->getId().']');
						$existingDropFolderFile->setStatus(DropFolderFileStatus::PURGED);				
						$existingDropFolderFile->save();
						
						$newDropFolderFile = $dbDropFolderFile->copy(true);
						if(	$existingDropFolderFile->getLeadDropFolderFileId() && 
							$existingDropFolderFile->getLeadDropFolderFileId() != $existingDropFolderFile->getId())
						{
							VidiunLog::info('Updating lead id ['.$existingDropFolderFile->getLeadDropFolderFileId().']');							
							$newDropFolderFile->setLeadDropFolderFileId($existingDropFolderFile->getLeadDropFolderFileId());	
						}
						$newDropFolderFile->save();
						$dbDropFolderFile = $newDropFolderFile;
				}
			}
			else 
			{
				throw $e;
			}
		}	
		// return the saved object
		$dropFolderFile = VidiunDropFolderFile::getInstanceByType($dbDropFolderFile->getType());
		$dropFolderFile->fromObject($dbDropFolderFile, $this->getResponseProfile());
		return $dropFolderFile;		
		
	}
	
}
