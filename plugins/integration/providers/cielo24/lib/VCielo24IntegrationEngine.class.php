<?php
/**
 * @package plugins.cielo24
 * @subpackage Scheduler
 */
class VCielo24IntegrationEngine implements VIntegrationCloserEngine
{
	private $baseEndpointUrl = null;
	private $clientHelper = null;
	
	const GET_URL_FILE_NAME = "vidiunFile";
	
	/* (non-PHPdoc)
	 * @see VIntegrationCloserEngine::dispatch()
	 */
	public function dispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data)
	{
		return $this->doDispatch($job, $data, $data->providerData);
	}
	
	/* (non-PHPdoc)
	 * @see VIntegrationCloserEngine::close()
	 */
	public function close(VidiunBatchJob $job, VidiunIntegrationJobData &$data)
	{
		return $this->doClose($job, $data, $data->providerData);
	}
	
	protected function doDispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunCielo24JobProviderData $providerData)
	{
		$entryId = $providerData->entryId;
		$flavorAssetId = $providerData->flavorAssetId;
		$spokenLanguage = $providerData->spokenLanguage;
		$priority = $providerData->priority;
		$fidelity = $providerData->fidelity;
	
		$formatsString = $providerData->captionAssetFormats;
		$formatsArray = explode(',', $formatsString);
	
		$shouldReplaceRemoteMedia = $providerData->replaceMediaContent;
		$callBackUrl = $data->callbackNotificationUrl;
		VidiunLog::debug('callback is - ' . $callBackUrl);

		$additionalParameters = json_decode($providerData->additionalParameters, true);
		$this->clientHelper = Cielo24Plugin::getClientHelper($providerData->username, $providerData->password, $providerData->baseUrl, $additionalParameters);
		
		//setting a pre-defined name to prevent the flavor-url to contain chars that will break the curl url syntax
		$nameOptions = new VidiunFlavorAssetUrlOptions();
		$nameOptions->fileName = self::GET_URL_FILE_NAME;	
		$flavorUrl = VBatchBase::$vClient->flavorAsset->getUrl($flavorAssetId, null, null, $nameOptions);

		$languageName = $this->clientHelper->getLanguageConstantName($spokenLanguage);
		$jobNameForSearch = $entryId . "_$languageName";

		if($shouldReplaceRemoteMedia == true)
		{
			$jobIds = $this->clientHelper->getRemoteJobIdByName($entryId, $jobNameForSearch . "*", true);
			foreach($jobIds as $remoteJobId)
				$this->clientHelper->deleteRemoteFile($remoteJobId);
		}

		$jobId = $job->id;
		$jobNameForUpload = $jobNameForSearch . "_$jobId";

		$uploadSuccess = $this->clientHelper->uploadMedia($flavorUrl, $entryId, $callBackUrl, $spokenLanguage, $priority, $fidelity, $jobNameForUpload);
		if(!$uploadSuccess)
			throw new Exception("upload failed");
	
		return false;
	}
	
	protected function doClose(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunCielo24JobProviderData $providerData)
	{
		return false;
	}
}
