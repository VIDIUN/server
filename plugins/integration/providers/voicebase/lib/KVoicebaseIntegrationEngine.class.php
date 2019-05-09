<?php
/**
 * @package plugins.voicebase
 * @subpackage Scheduler
 */
class VVoicebaseIntegrationEngine implements VIntegrationCloserEngine
{
	private $baseEndpointUrl = null;
	private $clientHelper = null;
	
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
	
	protected function doDispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunVoicebaseJobProviderData $providerData)
	{
		VidiunLog::info ("Starting dispatch - VoiceBase");
		$entryId = $providerData->entryId;
		$flavorAssetId = $providerData->flavorAssetId;
		$spokenLanguage = $providerData->spokenLanguage;
		$formatsString = $providerData->captionAssetFormats;
		$formatsArray = explode(',', $formatsString);

		$shouldReplaceRemoteMedia = $providerData->replaceMediaContent;
		$fileLocation = $providerData->fileLocation;
		$callBackUrl = $data->callbackNotificationUrl;
	
		VidiunLog::debug('callback is - ' . $callBackUrl);

		$additionalParameters = json_decode($providerData->additionalParameters, true);
		$this->clientHelper = VoicebasePlugin::getClientHelper($providerData->apiKey, $providerData->apiPassword, $additionalParameters);
		$flavorUrl = VBatchBase::$vClient->flavorAsset->getUrl($flavorAssetId);
	
		$externalId = $entryId . '_' . $job->id;
		$externalEntryExists = $this->clientHelper->checkExistingExternalContent($externalId);
		if (!$externalEntryExists)
		{
			$uploadSuccess = $this->clientHelper->uploadMedia($flavorUrl, $entryId, $externalId, $callBackUrl, $spokenLanguage, $fileLocation);
		}
		elseif($shouldReplaceRemoteMedia == true)
		{
			$this->clientHelper->deleteRemoteFile($externalId);
			$uploadSuccess = $this->clientHelper->uploadMedia($flavorUrl, $entryId, $externalId, $callBackUrl, $spokenLanguage, $fileLocation);

		}
		elseif($fileLocation)
		{
			$result = $this->clientHelper->updateRemoteTranscript($externalId, $fileLocation, $callBackUrl);
		}	
		else
		{
			return true;
		}

		return false;
	}
	
	protected function doClose(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunVoicebaseJobProviderData $providerData)
	{
		$entryId = $providerData->entryId;
		$this->clientHelper = VoicebasePlugin::getClientHelper($providerData->apiKey, $providerData->apiPassword);
		$remoteProcess = $this->clientHelper->retrieveRemoteProcess($entryId . '_' . $job->id);
		
		//false result means that something has gone wrong - the VB job is either in status error or missing altogether
		if(!$remoteProcess || $remoteProcess->requestStatus == VoicebaseClientHelper::VOICEBASE_FAILURE_MESSAGE || !isset($remoteProcess->fileStatus) || $remoteProcess->fileStatus == VoicebaseClientHelper::VOICEBASE_MACHINE_FAILURE_MESSAGE)
		{
			throw new Exception("VoiceBase transcription failed. Message: [" . $remoteProcess->response . "]");
		}
		
		if ($providerData->transcriptId && $remoteProcess->fileStatus == VoicebaseClientHelper::VOICEBASE_HUMAN_COMPLETE_MESSAGE)
		{
			return true;
		}
		elseif (!$providerData->transcriptId && $remoteProcess->fileStatus == VoicebaseClientHelper::VOICEBASE_MACHINE_COMPLETE_MESSAGE)
		{
			return true;
		}
		
		return false;
	}
}
