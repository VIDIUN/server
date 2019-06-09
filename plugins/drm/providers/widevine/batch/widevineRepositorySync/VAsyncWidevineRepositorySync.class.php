<?php

class VAsyncWidevineRepositorySync extends VJobHandlerWorker
{	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::WIDEVINE_REPOSITORY_SYNC;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->syncRepository($job, $job->data);			
	}

	protected function syncRepository(VidiunBatchJob $job, VidiunWidevineRepositorySyncJobData $data)
	{
		$job = $this->updateJob($job, "Start synchronization of Widevine repository", VidiunBatchJobStatus::QUEUED);
				
		switch ($data->syncMode)
		{
			case VidiunWidevineRepositorySyncMode::MODIFY:
				$this->sendModifyRequest($job, $data);
				break;
			default:
				throw new vApplicativeException(null, "Unknown sync mode [".$data->syncMode. "]");
		}

		return $this->closeJob($job, null, null, "Sync request sent successfully", VidiunBatchJobStatus::FINISHED, $data);
	}		

	/**
	 * Send asset notify request to VOD Dealer to update widevine assets
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunWidevineRepositorySyncJobData $data
	 */
	private function sendModifyRequest(VidiunBatchJob $job, VidiunWidevineRepositorySyncJobData $data)
	{
		$dataWrap = new WidevineRepositorySyncJobDataWrap($data);		
		$widevineAssets = $dataWrap->getWidevineAssetIds();
		$licenseStartDate = $dataWrap->getLicenseStartDate();
		$licenseEndDate = $dataWrap->getLicenseEndDate();

		$this->impersonate($job->partnerId);

		$drmPlugin = VidiunDrmClientPlugin::get(VBatchBase::$vClient);
		$profile = $drmPlugin->drmProfile->getByProvider(VidiunDrmProviderType::WIDEVINE);

		foreach ($widevineAssets as $assetId) 
		{
			$this->updateWidevineAsset($assetId, $licenseStartDate, $licenseEndDate, $profile);
		}
		
		if($data->monitorSyncCompletion)
			$this->updateFlavorAssets($job, $dataWrap);

		$this->unimpersonate();
	}
	
	/**
	 * Execute register asset with new details to update exisiting asset
	 * 
	 * @param int $assetId
	 * @param string $licenseStartDate
	 * @param string $licenseEndDate
	 * @throws vApplicativeException
	 */
	private function updateWidevineAsset($assetId, $licenseStartDate, $licenseEndDate, $profile)
	{
		VidiunLog::debug("Update asset [".$assetId."] license start date [".$licenseStartDate.'] license end date ['.$licenseEndDate.']');
		
		$errorMessage = '';
		
		$wvAssetId = VWidevineBatchHelper::sendRegisterAssetRequest(
										$profile->regServerHost,
										null,
										$assetId,
										$profile->portal,
										null,
										$licenseStartDate,
										$licenseEndDate,
										$profile->iv, 
										$profile->key, 									
										$errorMessage);				
		
		if(!$wvAssetId)
		{
			VBatchBase::unimpersonate();
			
			$logMessage = 'Asset update failed, asset id: '.$assetId.' error: '.$errorMessage;
			VidiunLog::err($logMessage);
			throw new vApplicativeException(null, $logMessage);
		}			
	}
	
	/**
	 * Update flavorAsset in Vidiun after the distribution dates apllied to Wideivne asset
	 * 
	 * @param VidiunBatchJob $job
	 * @param WidevineRepositorySyncJobDataWrap $dataWrap
	 */
	private function updateFlavorAssets(VidiunBatchJob $job, WidevineRepositorySyncJobDataWrap $dataWrap)
	{	
		$startDate = $dataWrap->getLicenseStartDate();
		$endDate = $dataWrap->getLicenseEndDate();	
		
		$filter = new VidiunAssetFilter();
		$filter->entryIdEqual = $job->entryId;
		$filter->tagsLike = 'widevine';
		$flavorAssetsList = self::$vClient->flavorAsset->listAction($filter, new VidiunFilterPager());
		
		foreach ($flavorAssetsList->objects as $flavorAsset) 
		{
			if($flavorAsset instanceof VidiunWidevineFlavorAsset && $dataWrap->hasAssetId($flavorAsset->widevineAssetId))
			{
				$updatedFlavorAsset = new VidiunWidevineFlavorAsset();
				$updatedFlavorAsset->widevineDistributionStartDate = $startDate;
				$updatedFlavorAsset->widevineDistributionEndDate = $endDate;
				self::$vClient->flavorAsset->update($flavorAsset->id, $updatedFlavorAsset);
			}		
		}		
	}
}
