<?php
/**
 * Event consumer which finishes up the export process to Kontiki
 */
class vKontikiManager implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob) 
	{
		switch ($dbBatchJob->getStatus()) {
			case BatchJob::BATCHJOB_STATUS_FINISHED:
				$data = $dbBatchJob->getData();
				$kontikiFileSync = FileSyncPeer::retrieveByPK($data->getSrcFileSyncId());
                /* @var $data vStorageExportJobData */
                $asset = assetPeer::retrieveByFileSync($kontikiFileSync);
                $asset->setTags(KontikiPlugin::KONTIKI_ASSET_TAG);
                $asset->save();
                //Get Kontiki file sync and set the external URL
                $kontikiFileSync->setFileRoot("");
                $kontikiFileSync->setFilePath($data->getContentMoid());
                $kontikiFileSync->save();
            break;
		}

		return true;
		
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob) {
		if ($dbBatchJob->getJobType() == BatchJobType::STORAGE_EXPORT
            && $dbBatchJob->getJobSubType() == KontikiPlugin::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI))
		{
			if (KontikiPlugin::isAllowedPartner($dbBatchJob->getPartnerId()))
		    	return true;
		}
        
        return false;
	}


}