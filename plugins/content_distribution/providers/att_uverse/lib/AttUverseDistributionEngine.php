<?php
/**
 * @package plugins.attUverseDistribution
 * @subpackage lib
 */
class AttUverseDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit	
{
	
	const FEED_TEMPLATE = 'feed_template.xml';

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunAttUverseDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunAttUverseDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunAttUverseDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunAttUverseDistributionJobProviderData");
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunAttUverseDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunAttUverseDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunUverseDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUverseDistributionJobProviderData");
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunAttUverseDistributionProfile $distributionProfile
	 * @param VidiunAttUverseDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunAttUverseDistributionProfile $distributionProfile, VidiunAttUverseDistributionJobProviderData $providerData)
	{
		/* @var $entryDistribution EntryDistribution */
		$entryDistribution = $data->entryDistribution;	

		$remoteId = $entryDistribution->entryId;
		$data->remoteId = $remoteId;
							
		$ftpManager = $this->getFTPManager($distributionProfile);
		if(!$ftpManager)
			throw new Exception("FTP manager not loaded");		
			
		//upload video to FTP
		$remoteAssetFileUrls = array();
		$remoteThumbnailFileUrls = array();
		$remoteCaptionFileUrls = array();
		/* @var $file VidiunAttUverseDistributionFile */
		foreach ($providerData->filesForDistribution as $file){
			$ftpPath = $distributionProfile->ftpPath;
			$destFilePath = $ftpPath ?  $ftpPath.DIRECTORY_SEPARATOR.$file->remoteFilename: $file->remoteFilename;	
			$this->uploadAssetsFiles($ftpManager, $destFilePath, $file->localFilePath);
			if ($file->assetType == VidiunAssetType::FLAVOR)
				$remoteAssetFileUrls[$file->assetId] = 'ftp://'.$distributionProfile->ftpHost.'/'.$destFilePath;
			if ( $file->assetType == VidiunAssetType::THUMBNAIL)
				$remoteThumbnailFileUrls[$file->assetId] = 'ftp://'.$distributionProfile->ftpHost.'/'.$destFilePath;
			if ( ($file->assetType == VidiunAssetType::ATTACHMENT) ||($file->assetType == VidiunAssetType::CAPTION))
				$remoteCaptionFileUrls[$file->assetId] = 'ftp://'.$distributionProfile->ftpHost.'/'.$destFilePath;
		}
		
		//save flavor assets on provider data to use in the service				
		$providerData->remoteAssetFileUrls = serialize($remoteAssetFileUrls);
		//save thumnail assets on provider data to use in the service
		$providerData->remoteThumbnailFileUrls = serialize($remoteThumbnailFileUrls);
		//save caption assets on provider data to use in the service
		$providerData->remoteCaptionFileUrls = serialize($remoteCaptionFileUrls);
		

	}	
	
	/**
	 * 
	 * @param VidiunAttUverseDistributionProfile $distributionProfile
	 * @return ftpMgr
	 */
	protected function getFTPManager(VidiunAttUverseDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->ftpHost;
		$login = $distributionProfile->ftpUsername;
		$password = $distributionProfile->ftpPassword;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		$ftpManager->login($host, $login, $password);
		return $ftpManager;
	}
	
	
	protected function uploadAssetsFiles($ftpManager, $destFileName, $filePath)
	{									
		if ($ftpManager->fileExists($destFileName))
		{
			VidiunLog::err('The file ['.$destFileName.'] already exists at the FTP');
		}
		else	
		{					
			$ftpManager->putFile($destFileName, $filePath, true);
		}
	}
	

}