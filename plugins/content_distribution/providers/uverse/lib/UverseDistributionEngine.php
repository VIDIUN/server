<?php
/**
 * @package plugins.uverseDistribution
 * @subpackage lib
 */
class UverseDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineDelete
{
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUverseDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUverseDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunUverseDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUverseDistributionJobProviderData");
		
		$this->sendFile($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUverseDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUverseDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunUverseDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUverseDistributionJobProviderData");
		
		$this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUverseDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUverseDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunUverseDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUverseDistributionJobProviderData");
		
		$this->sendFile($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUverseDistributionProfile $distributionProfile
	 * @param VidiunUverseDistributionJobProviderData $providerData
	 */
	protected function sendFile(VidiunDistributionJobData $data, VidiunUverseDistributionProfile $distributionProfile, VidiunUverseDistributionJobProviderData $providerData)
	{
		$ftpManager = $this->getFTPManager($distributionProfile);
		
		$providerData->remoteAssetFileName = $this->getRemoteFileName($distributionProfile, $providerData);
		$providerData->remoteAssetUrl = $this->getRemoteUrl($distributionProfile, $providerData);
		if ($ftpManager->fileExists($providerData->remoteAssetFileName))
			VidiunLog::err('The file ['.$providerData->remoteAssetFileName.'] already exists at the FTP');
		else
			$ftpManager->putFile($providerData->remoteAssetFileName, $providerData->localAssetFilePath);
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUverseDistributionProfile $distributionProfile
	 * @param VidiunUverseDistributionJobProviderData $providerData
	 */
	protected function handleDelete(VidiunDistributionJobData $data, VidiunUverseDistributionProfile $distributionProfile, VidiunUverseDistributionJobProviderData $providerData)
	{
		$ftpManager = $this->getFTPManager($distributionProfile);
		$ftpManager->delFile($providerData->remoteAssetFileName);
	}
	
	/**
	 * 
	 * @param VidiunUverseDistributionProfile $distributionProfile
	 * @return ftpMgr
	 */
	protected function getFTPManager(VidiunUverseDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->ftpHost;
		$login = $distributionProfile->ftpLogin;
		$password = $distributionProfile->ftpPassword;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		$ftpManager->login($host, $login, $password);
		return $ftpManager;
	}
	
	/**
	 * @param VidiunUverseDistributionProfile $distributionProfile
	 * @param VidiunUverseDistributionJobProviderData $providerData
	 * @return string
	 */
	protected function getRemoteFileName(VidiunUverseDistributionProfile $distributionProfile, VidiunUverseDistributionJobProviderData $providerData)
	{
		return pathinfo($providerData->localAssetFilePath, PATHINFO_BASENAME);
	}
	
	/**
	 * @param VidiunUverseDistributionProfile $distributionProfile
	 * @param VidiunUverseDistributionJobProviderData $providerData
	 * @return string
	 */
	protected function getRemoteUrl(VidiunUverseDistributionProfile $distributionProfile, VidiunUverseDistributionJobProviderData $providerData)
	{
		$remoteFileName = $this->getRemoteFileName($distributionProfile, $providerData);
		return 'ftp://'.$distributionProfile->ftpHost.'/'.$remoteFileName;
	}
}