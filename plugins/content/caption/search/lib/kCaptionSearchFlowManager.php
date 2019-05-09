<?php
/**
 * @package plugins.captionSearch
 * @subpackage lib
 */
class vCaptionSearchFlowManager implements vObjectDataChangedEventConsumer, vObjectDeletedEventConsumer, vObjectAddedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeAddedEvent
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if(class_exists('CaptionAsset') && $object instanceof CaptionAsset 
				&& CaptionSearchPlugin::isAllowedPartner($object->getPartnerId())
				&& $object->getStatus() == CaptionAsset::ASSET_STATUS_READY && $object->getLanguage() != CaptionAsset::MULTI_LANGUAGE){
						return true;
					}
					
		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::objectAdded
	 */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		return $this->indexEntry($object, $raisedJob);
	}

	/* (non-PHPdoc)
	 * @see vObjectDataChangedEventConsumer::shouldConsumeDataChangedEvent()
	 */
	public function shouldConsumeDataChangedEvent(BaseObject $object, $previousVersion = null)
	{
		if(class_exists('CaptionAsset') && $object instanceof CaptionAsset && $object->getLanguage() != CaptionAsset::MULTI_LANGUAGE)
			return CaptionSearchPlugin::isAllowedPartner($object->getPartnerId());
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectDataChangedEventConsumer::objectDataChanged()
	 */
	public function objectDataChanged(BaseObject $object, $previousVersion = null, BatchJob $raisedJob = null)
	{
		return $this->indexEntry($object, $raisedJob);
	}
	
	private function indexEntry(BaseObject $object, BatchJob $raisedJob = null)
	{
		// updated in the entry in the indexing server
		$entry = $object->getentry();
		if($entry)
		{
			$entry->setUpdatedAt(time());
			$entry->save();
			$entry->indexToSearchIndex();
		}

		return true;
	}
	
	/**
	 * @param CaptionAsset $captionAsset
	 * @param BatchJob $parentJob
	 * @throws vCoreException FILE_NOT_FOUND
	 * @return BatchJob
	 */
	public function addParseCaptionAssetJob(CaptionAsset $captionAsset, BatchJob $parentJob = null)
	{
		$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$fileSync = vFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
		if(!$fileSync)
		{
			if(!PermissionPeer::isValidForPartner(CaptionPermissionName::IMPORT_REMOTE_CAPTION_FOR_INDEXING, $captionAsset->getPartnerId()))
				throw new vCoreException("File sync not found: $syncKey", vCoreException::FILE_NOT_FOUND);
			
			$fileSync = vFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
			if(!$fileSync)
				throw new vCoreException("File sync not found: $syncKey", vCoreException::FILE_NOT_FOUND);
			
	    	$fullPath = myContentStorage::getFSUploadsPath() . '/' . $captionAsset->getId() . '.tmp';
			if(!VCurlWrapper::getDataFromFile($fileSync->getExternalUrl($captionAsset->getEntryId()), $fullPath, null, true))
				throw new vCoreException("File sync not found: $syncKey", vCoreException::FILE_NOT_FOUND);
			
			vFileSyncUtils::moveFromFile($fullPath, $syncKey, true, false, true);
		}
		
		$jobData = new vParseCaptionAssetJobData();
		$jobData->setCaptionAssetId($captionAsset->getId());
			
 		$batchJobType = CaptionSearchPlugin::getBatchJobTypeCoreValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET);
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild($batchJobType);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($captionAsset->getEntryId());
			$batchJob->setPartnerId($captionAsset->getPartnerId());
		}
			
		$batchJob->setObjectId($captionAsset->getId());
		$batchJob->setObjectType(BatchJobObjectType::ASSET);
		return vJobsManager::addJob($batchJob, $jobData, $batchJobType);
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		/* @var $object CaptionAsset */
		// updates entry on order to trigger reindexing
		$this->indexEntry($object);
		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof CaptionAsset && $object->getLanguage() != CaptionAsset::MULTI_LANGUAGE)
			return true;
			
		return false;
	}
}
