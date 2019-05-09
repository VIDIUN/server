<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage lib
 */
class MetroPcsDistributionEngine extends DistributionEngine implements 
	IDistributionEngineSubmit,
	IDistributionEngineCloseSubmit,
	IDistributionEngineUpdate,
	IDistributionEngineCloseUpdate,
	IDistributionEngineDelete,
	IDistributionEngineCloseDelete
{
	const FEED_TEMPLATE = 'feed_template.xml';
	
	const METRO_PCS_STATUS_PUBLISHED = 'PUBLISHED';
	const METRO_PCS_STATUS_PENDING = 'PENDING';
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		
		// metro pcs didn't approve that this logic does work, for now just mark every submited xml as successful
		return true;
		/*
		$publishState = $this->fetchStatus($data);
		switch($publishState)
		{
			case self::METRO_PCS_STATUS_PUBLISHED:
				return true;
			case self::METRO_PCS_STATUS_PENDING:
				return false;
			default:
				throw new Exception("Error [$publishState]");
		}
		*/
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
	 * (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		
		$this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		return true;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 */
	protected function validateJobDataObjectTypes(VidiunDistributionJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunMetroPcsDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunMetroPcsDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunMetroPcsDistributionJobProviderData))
			throw new Exception("Provider data must be of type VidiunMetroPcsDistributionJobProviderData");
	}
	
	/**
	 * @param string $path
	 * @param VidiunDistributionJobData $data
	 * @param VidiunMetroPcsDistributionProfile $distributionProfile
	 * @param VidiunMetroPcsDistributionJobProviderData $providerData
	 */
	public function handleSubmit(VidiunDistributionJobData $data, VidiunMetroPcsDistributionProfile $distributionProfile, VidiunMetroPcsDistributionJobProviderData $providerData)
	{
		$entryDistribution = $data->entryDistribution;
			
		//getting first flavor
		$flavorAssetsLocalPaths = unserialize($providerData->assetLocalPaths);
		//getting thumbnail urls
		$thumbUrls = unserialize($providerData->thumbUrls);
		reset($flavorAssetsLocalPaths);
		$firstFlavorAssetId = key($flavorAssetsLocalPaths);
		$firstFlavorAssetPath = $flavorAssetsLocalPaths[$firstFlavorAssetId];
		$flavorAssetArray = $this->getFlavorAsset($entryDistribution, $firstFlavorAssetId);	
		$flavorAsset = 	$flavorAssetArray[0];
		//getting thumbnails
		$thumbAssets = $this->getEntryDistributionThumbAssets($entryDistribution);
		$entryDuration = $this->getEntryDuration($entryDistribution);
		
		//building feed
		$currentTime = date('Y-m-d_H-i-s');
		$feed = new MetroPcsDistributionFeedHelper(self::FEED_TEMPLATE, $entryDistribution, $distributionProfile, $providerData);	
		$feed->setFlavor($flavorAsset, $entryDuration, $currentTime);
		$feed->setThumbnails($thumbAssets, $thumbUrls);
		
		//xml file
		$xmlFileName = $currentTime. '_' .$entryDistribution->id. '_' .$data->entryDistribution->entryId .'.xml';
		$path = $distributionProfile->ftpPath;
		$destXmlFile = "{$path}/{$xmlFileName}";		
		$xmlString = $feed->getXmlString();
		
		VidiunLog::info('result xml - '.PHP_EOL.$xmlString);
		$tempFile = vFile::createTempFile($xmlString, 'tmp');
		
		//load the FTP
		$ftpManager = $this->getFTPManager($distributionProfile);
		if(!$ftpManager)
			throw new Exception("FTP manager not loaded");		
			
		//upload flavor file to FTP	
		$this->uploadFlavorAssetFile($path, $feed, $providerData, $ftpManager, $flavorAsset, $currentTime);
			
		//upload feed xml file to FTP
		$ftpManager->putFile($destXmlFile, $tempFile, true);			
		
		$data->remoteId = $xmlFileName;
		$data->sentData = $xmlString;
	}	
	
	/**
	 * @param string $path
	 * @param VidiunDistributionJobData $data
	 * @param VidiunMetroPcsDistributionProfile $distributionProfile
	 * @param VidiunMetroPcsDistributionJobProviderData $providerData
	 */
	public function handleDelete(VidiunDistributionJobData $data, VidiunMetroPcsDistributionProfile $distributionProfile, VidiunMetroPcsDistributionJobProviderData $providerData)
	{
		$entryDistribution = $data->entryDistribution;
		$entryDuration = $this->getEntryDuration($entryDistribution);
		
		//building feed
		$currentTime = date('Y-m-d_H-i-s');
		$feed = new MetroPcsDistributionFeedHelper(self::FEED_TEMPLATE, $entryDistribution, $distributionProfile, $providerData);	
		//set end time and start time
		$feed->setTimesForDelete();
		//ignoring the image and item tags
		$feed->setImageIgnore();
		$feed->setItemIgnore();
		
		//xml file
		$xmlFileName = $currentTime. '_' .$entryDistribution->id. '_' .$data->entryDistribution->entryId .'.xml';		
		$path = $distributionProfile->ftpPath;
		$destXmlFile = "{$path}/{$xmlFileName}";		
		$xmlString = $feed->getXmlString();	
		VidiunLog::info('result xml - '.PHP_EOL.$xmlString);
		$tempFile = vFile::createTempFile($xmlString, 'tmp');
	
		//load the FTP
		$ftpManager = $this->getFTPManager($distributionProfile);
		if(!$ftpManager)
			throw new Exception("FTP manager not loaded");		
			
		//upload feed xml file to FTP
		$ftpManager->putFile($destXmlFile, $tempFile, true);			
		
		$data->remoteId = $xmlFileName;
		$data->sentData = $xmlString;
	}
	
	/**
	 * 
	 * @param VidiunMetroPcsDistributionProfile $distributionProfile
	 * @return ftpMgr
	 */
	protected function getFTPManager(VidiunMetroPcsDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->ftpHost;
		$login = $distributionProfile->ftpLogin;
		$pass = $distributionProfile->ftpPass;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		$ftpManager->login($host, $login, $pass);
		return $ftpManager;
	}
	
	/**
	 * @param VidiunDistributionSubmitJobData $data
	 * @return string status
	 */
	protected function fetchStatus(VidiunDistributionJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunMetroPcsDistributionProfile))
			return VidiunLog::err("Distribution profile must be of type VidiunMetroPcsDistributionProfile");
	
		$fileArray = $this->fetchFilesList($data->distributionProfile);
		
		for	($i=0; $i<count($fileArray); $i++)
		{
			if (preg_match ( "/{$data->remoteId}.rcvd/" , $fileArray[$i] , $matches))
			{
				return self::METRO_PCS_STATUS_PUBLISHED;
			}
			else if (preg_match ( "/{$data->remoteId}.*.err/" , $fileArray[$i] , $matches))
			{
				//$res = preg_split ("/\./", $matches[0]);
				//return $res[1];
				$res = explode('.',$matches[0]);
				return $res[1];			
			}
		}

		return self::METRO_PCS_STATUS_PENDING;
	}

	/**
	 * @param VidiunMetroPcsDistributionProfile $distributionProfile
	 */
	protected function fetchFilesList(VidiunMetroPcsDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->ftpHost;
		$login = $distributionProfile->ftpLogin;
		$pass = $distributionProfile->ftpPass;
		
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$fileTransferMgr = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		if(!$fileTransferMgr)
			throw new Exception("FTP manager not loaded");
			
		$fileTransferMgr->login($host, $host, $pass);
		return $fileTransferMgr->listDir('/');
	}
	
	protected function getEntryDistributionThumbAssets(VidiunEntryDistribution $entryDistribution)
	{
		$thumbAssetIds = $entryDistribution->thumbAssetIds;
		$partnerId = $entryDistribution->partnerId;
		
		return parent::getThumbAssets($partnerId, $thumbAssetIds);
	}
	
	protected function getFlavorAsset(VidiunEntryDistribution $entryDistribution, $flavorAssetId)
	{
		$flavorAssetFilter = new VidiunFlavorAssetFilter();
		$flavorAssetFilter->entryIdEqual = $entryDistribution->entryId;
		$flavorAssetFilter->idIn = $flavorAssetId;
		
		try {
			VBatchBase::impersonate($entryDistribution->partnerId);
			$flavorAssets = VBatchBase::$vClient->flavorAsset->listAction($flavorAssetFilter);
			VBatchBase::unimpersonate();
		}
		catch (Exception $e) {
			VBatchBase::unimpersonate();
			throw $e;
		}		
		return $flavorAssets->objects;		
	}
	
	protected function getEntryDuration(VidiunEntryDistribution $entryDistribution)
	{		
		try {
			VBatchBase::impersonate($entryDistribution->partnerId);
			$entry = VBatchBase::$vClient->baseEntry->get($entryDistribution->entryId);
			VBatchBase::unimpersonate();
		}
		catch (Exception $e) {
			VBatchBase::unimpersonate();
			throw $e;
		}
		
		return $entry->duration;
	}
	
	protected function uploadFlavorAssetFile($path, $feed, $providerData, $ftpManager, $flavorAsset, $currentTime)
	{
		$destName = $feed->flavorAssetUniqueName($flavorAsset, $currentTime);
		//adding the ftp path to the dest name 
		$destName = $path.'/'.$destName;
		
		$videoAssetFilePathArray = unserialize($providerData->assetLocalPaths);
		$sourceName = $videoAssetFilePathArray[$flavorAsset->id];
		$ftpManager->putFile($destName, $sourceName, true);
	}
	
}