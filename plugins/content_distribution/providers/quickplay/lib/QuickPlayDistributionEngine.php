<?php
/**
 * @package plugins.quickPlayDistribution
 * @subpackage lib
 */
class QuickPlayDistributionEngine extends DistributionEngine implements 
	IDistributionEngineSubmit,
	IDistributionEngineUpdate
{
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 */
	protected function validateJobDataObjectTypes(VidiunDistributionJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunQuickPlayDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunQuickPlayDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunQuickPlayDistributionJobProviderData))
			throw new Exception("Provider data must be of type VidiunQuickPlayDistributionJobProviderData");
	}
	
	/**
	 * @param string $path
	 * @param VidiunDistributionJobData $data
	 * @param VidiunVerizonDistributionProfile $distributionProfile
	 * @param VidiunVerizonDistributionJobProviderData $providerData
	 */
	public function handleSubmit(VidiunDistributionJobData $data, VidiunQuickPlayDistributionProfile $distributionProfile, VidiunQuickPlayDistributionJobProviderData $providerData)
	{
		$fileName = $data->entryDistribution->entryId . '_' . date('Y-m-d_H-i-s') . '.xml';
		VidiunLog::info('Sending file '. $fileName);
		
		$sftpManager = $this->getSFTPManager($distributionProfile);
		
		// upload the thumbnails
		foreach($providerData->thumbnailFilePaths as $thumbnailFilePath)
		{
			/* @var $thumbnailFilePath VidiunString */
			if (!file_exists($thumbnailFilePath->value))
				throw new VidiunDistributionException('Thumbnail file path ['.$thumbnailFilePath.'] not found, assuming it wasn\'t synced and the job will retry');

			$thumbnailUploadPath = '/'.$distributionProfile->sftpBasePath.'/'.pathinfo($thumbnailFilePath->value, PATHINFO_BASENAME);
			if ($sftpManager->fileExists($thumbnailUploadPath))
				VidiunLog::info('File "'.$thumbnailUploadPath.'" already exists, skip it');
			else
				$sftpManager->putFile($thumbnailUploadPath, $thumbnailFilePath->value);
		}
		
		// upload the video files
		foreach($providerData->videoFilePaths as $videoFilePath)
		{
			/* @var $videoFilePath VidiunString */
			if (!file_exists($videoFilePath->value))
				throw new VidiunDistributionException('Video file path ['.$videoFilePath.'] not found, assuming it wasn\'t synced and the job will retry');

			$videoUploadPath = '/'.$distributionProfile->sftpBasePath.'/'.pathinfo($videoFilePath->value, PATHINFO_BASENAME);
			if ($sftpManager->fileExists($videoUploadPath))
				VidiunLog::info('File "'.$videoUploadPath.'" already exists, skip it');
			else
				$sftpManager->putFile($videoUploadPath, $videoFilePath->value);
		}

		$tmpfile = tempnam(sys_get_temp_dir(), time());
		file_put_contents($tmpfile, $providerData->xml);
		// upload the metadata file
		$res = $sftpManager->putFile('/'.$distributionProfile->sftpBasePath.'/'.$fileName, $tmpfile);
		unlink($tmpfile);
				
		if ($res === false)
			throw new Exception('Failed to upload metadata file to sftp');
			
		$data->remoteId = $fileName;
		$data->sentData = $providerData->xml;
	}
	
	/**
	 * 
	 * @param VidiunQuickPlayDistributionProfile $distributionProfile
	 * @return sftpMgr
	 */
	protected function getSFTPManager(VidiunQuickPlayDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->sftpHost;
		$login = $distributionProfile->sftpLogin;
		$pass = $distributionProfile->sftpPass;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$sftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP, $engineOptions);
		$sftpManager->login($host, $login, $pass);
		return $sftpManager;
	}
}