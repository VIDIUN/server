<?php
class vVirusScanFlowManager implements vBatchJobStatusEventConsumer, vObjectAddedEventConsumer
{
	
	private static $assetIdsToScan = array();
	
	
	private function resumeEvents($flavorAsset, BatchJob $raisedJob = null)
	{
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $syncKey );
		$fileSyncList = FileSyncPeer::doSelect( $c );
				
		foreach ($fileSyncList as $fileSync)
		{
			// resume file sync added event
			vEventsManager::continueEvent(new vObjectAddedEvent($fileSync), 'vVirusScanFlowManager');
		}

		// resume flavor asset added event consumption
		vEventsManager::continueEvent(new vObjectAddedEvent($flavorAsset), 'vVirusScanFlowManager');
	}
	
	
	private function saveIfShouldScan($asset)
	{
		if (!PermissionPeer::isAllowedPlugin(VirusScanPlugin::PLUGIN_NAME, $asset->getPartnerId()))
			return false;
		
		if (isset(self::$assetIdsToScan[$asset->getId()]))
		{
			return true;
		}
		
		$profile = VirusScanProfilePeer::getSuitableProfile($asset->getEntryId());
		if ($profile)
		{
			self::$assetIdsToScan[$asset->getId()] = $profile;
			return true;
		}
		
		return false;
	}
	
	/**
	 * @param FileSync $object
	 * @return bool true if should continue to the next consumer
	 */
	private function addedFileSync(FileSync $object)
	{
		if(!($object instanceof FileSync) || $object->getStatus() != FileSync::FILE_SYNC_STATUS_PENDING || $object->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_FILE)
			return true;
			
		if ($object->getObjectType() != FileSyncObjectType::FLAVOR_ASSET)
			return true;
		
		$flavorAssetId = $object->getObjectId();
		$flavorAsset = assetPeer::retrieveById($flavorAssetId);
		if (!$flavorAsset || !$flavorAsset->getIsOriginal())
			return true;

		if ($this->saveIfShouldScan($flavorAsset))
		{
			// file sync belongs to a flavor asset in status pending and suits a virus scan profile
			return false; // stop all remaining consumers
		}			
		
		return true;
	}
	
	/**
	 * @param asset $object
	 * @return bool true if should continue to the next consumer
	 */
	private function addedAsset(asset $object, BatchJob $raisedJob = null)
	{
		if(($object instanceof flavorAsset && $object->getIsOriginal()) || $object instanceof AttachmentAsset)
		{
			if ($this->saveIfShouldScan($object))
			{
				$profile = self::$assetIdsToScan[$object->getId()];
				
				// suitable virus scan profile found - create scan job
				$syncKey = $object->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
				vVirusScanJobsManager::addVirusScanJob($raisedJob, $object->getPartnerId(), $object->getEntryId(), $object->getId(), $syncKey, $profile->getEngineType(), $profile->getActionIfInfected());
				return false; // pause other event consumers until virus scan job is finished
			}
		}
		
		return true; // no scan jobs to do, object added event consumption may continue normally
	}
	
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeAddedEvent()
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		// virus scan only works in api_v3 context because it uses dynamic enums
		if (!class_exists('vCurrentContext') || !vCurrentContext::isApiV3Context())
			return false;
		if($object instanceof asset)
		{
			$entry = $object->getentry();
			if ($entry && $entry->getFlowType() == EntryFlowType::IMPORT_FOR_CLIP_CONCAT )
			{
				return false;
			}
			return true;
		}
		if($object instanceof FileSync)
			return true;
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::objectAdded()
	 */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		$response = true;
		if($object instanceof asset)
		{
			$response = $this->addedAsset($object, $raisedJob);
		}
		
		if($object instanceof FileSync)
		{
			$response = $this->addedFileSync($object);
		}
		
		if (!$response) {
			VidiunLog::info('Stopping consumption of event ['.get_class($object).']');
		}
		return $response;	
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if (!class_exists('vCurrentContext') || !vCurrentContext::isApiV3Context())
			return false;
			
		if($dbBatchJob->getJobType() == VirusScanPlugin::getBatchJobTypeCoreValue(VirusScanBatchJobType::VIRUS_SCAN))
			return true;
				
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		$dbBatchJob = $this->updatedVirusScan($dbBatchJob, $dbBatchJob->getData());

		return true;
	}
		
	protected function updatedVirusScan(BatchJob $dbBatchJob, vVirusScanJobData $data)
	{
		switch($dbBatchJob->getStatus())
		{
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				return $this->updatedVirusScanFinished($dbBatchJob, $data);
			case BatchJob::BATCHJOB_STATUS_FAILED:
			case BatchJob::BATCHJOB_STATUS_FATAL:
				return $this->updatedVirusScanFailed($dbBatchJob, $data);
			default:
				return $dbBatchJob;
		}
	}
	
	protected function updatedVirusScanFinished(BatchJob $dbBatchJob, vVirusScanJobData $data)
	{
		$flavorAsset = assetPeer::retrieveById($data->getFlavorAssetId());
		if (!$flavorAsset)
		{
			VidiunLog::err('Flavor asset not found with id ['.$data->getFlavorAssetId().']');
			throw new Exception('Flavor asset not found with id ['.$data->getFlavorAssetId().']');
		}
				
		switch ($data->getScanResult())
		{
			case VidiunVirusScanJobResult::FILE_WAS_CLEANED:									
			case VidiunVirusScanJobResult::FILE_IS_CLEAN:
			    $entry = $flavorAsset->getentry();
			    if ($entry->getStatus() == VirusScanPlugin::getEntryStatusCoreValue(VirusScanEntryStatus::SCAN_FAILURE))
			    {
			        $entryStatusBeforeScanFailure = self::getEntryStatusBeforeScanFailure($entry);
			        if (!is_null($entryStatusBeforeScanFailure)) {
			            $entry->setStatus($entryStatusBeforeScanFailure);
			            self::setEntryStatusBeforeScanFailure($entry, null);
			            $entry->save();
			        }
			        $flavorAssetStatusBeforeScanFailure = self::getFlavorAssetStatusBeforeScanFailure($flavorAsset);    
			        if (!is_null($flavorAssetStatusBeforeScanFailure)) {
    			        $flavorAsset->setStatus($flavorAssetStatusBeforeScanFailure);
    			        self::setFlavorAssetStatusBeforeScanFailure($flavorAsset, null);
    			        $flavorAsset->save();
			        }
			    }
				$this->resumeEvents($flavorAsset, $dbBatchJob);
				break;
				
			case VidiunVirusScanJobResult::FILE_INFECTED:
				$entry = $flavorAsset->getentry();
				if (!$entry) {
					VidiunLog::err('Entry not found with id ['.$entry->getId().']');
				}
				else {
					$entry->setStatus(VirusScanPlugin::getEntryStatusCoreValue(VirusScanEntryStatus::INFECTED));
					$entry->save();
				}
				
				// delete flavor asset and entry if defined in virus scan profile	
				if ( $data->getVirusFoundAction() == VidiunVirusFoundAction::CLEAN_DELETE ||
					 $data->getVirusFoundAction() == VidiunVirusFoundAction::DELETE          )
				{
					$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
					$filePath = vFileSyncUtils::getLocalFilePathForKey($syncKey);
					VidiunLog::info('FlavorAsset ['.$flavorAsset->getId().'] marked as deleted');
					$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_DELETED);
					$flavorAsset->setDeletedAt(time());
					$flavorAsset->save();
					VidiunLog::info('Physically deleting file ['.$filePath.']');
					unlink($filePath);
					if ($entry)	{
						myEntryUtils::deleteEntry($entry);
					}
				}
				else {
					$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
					$flavorAsset->save();
				}				
				
				myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $entry);
				// do not resume flavor asset added event consumption
				break;
		}		
		
		return $dbBatchJob;
	}
	
	protected function updatedVirusScanFailed(BatchJob $dbBatchJob, vVirusScanJobData $data)
	{
		$entry = entryPeer::retrieveByPKNoFilter($dbBatchJob->getEntryId());
		if ($entry)
		{
		    self::setEntryStatusBeforeScanFailure($entry, $entry->getStatus());
			$entry->setStatus(VirusScanPlugin::getEntryStatusCoreValue(VirusScanEntryStatus::SCAN_FAILURE));
			$entry->save();
			myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $entry);
		}
		else
		{
			VidiunLog::err('Entry not found with id ['.$dbBatchJob->getEntryId().']');
			throw new Exception('Entry not found with id ['.$dbBatchJob->getEntryId().']');
		}
		$flavorAsset = assetPeer::retrieveById($data->getFlavorAssetId());
		if ($flavorAsset)
		{
		    self::setFlavorAssetStatusBeforeScanFailure($flavorAsset, $flavorAsset->getStatus());
			$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
			$flavorAsset->save();
		}
		else
		{
			VidiunLog::err('Flavor asset not found with id ['.$data->getFlavorAssetId().']');
			throw new Exception('Flavor asset not found with id ['.$data->getFlavorAssetId().']');
		}					
		// do not resume flavor asset added event consumption
		return $dbBatchJob;
	}
	
	
	const CUSTOM_DATA_STATUS_BEFORE_SCAN_FAILURE = 'status_before_scan_failure';
	
	
	protected static function setEntryStatusBeforeScanFailure(entry $entry, $status)
	{
	    $entry->putInCustomData(VirusScanPlugin::getPluginName().'_'.self::CUSTOM_DATA_STATUS_BEFORE_SCAN_FAILURE, $status);
	}
	
	protected static function getEntryStatusBeforeScanFailure(entry $entry)
	{
	    return $entry->getFromCustomData(VirusScanPlugin::getPluginName().'_'.self::CUSTOM_DATA_STATUS_BEFORE_SCAN_FAILURE);
	}
	
    protected static function setFlavorAssetStatusBeforeScanFailure(flavorAsset $flavorAsset, $status)
	{
	    $flavorAsset->putInCustomData(VirusScanPlugin::getPluginName().'_'.self::CUSTOM_DATA_STATUS_BEFORE_SCAN_FAILURE, $status);
	}
	
	protected static function getFlavorAssetStatusBeforeScanFailure(flavorAsset $flavorAsset)
	{
	    return $flavorAsset->getFromCustomData(VirusScanPlugin::getPluginName().'_'.self::CUSTOM_DATA_STATUS_BEFORE_SCAN_FAILURE);
	}
	
}
