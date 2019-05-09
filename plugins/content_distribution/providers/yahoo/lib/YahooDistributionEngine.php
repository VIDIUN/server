<?php
/**
 * @package plugins.yahooDistribution
 * @subpackage lib
 */
class YahooDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	//IDistributionEngineReport,
	IDistributionEngineDelete,
	IDistributionEngineCloseUpdate,
	IDistributionEngineCloseSubmit,
	IDistributionEngineCloseDelete
{
	const TEMP_DIRECTORY = 'yahoo_distribution';
	const FEED_TEMPLATE = 'feed_template.xml';
	const DELETE_FEED_TEMPLATE = 'feed_template_delete.xml';
	const ACCESS_URL = 'https://contentmgmt.bcst.yahoo.com/vbs_script_batchjob.asp';
	
	protected $tempXmlPath;

	/* (non-PHPdoc)
	 * @see DistributionEngine::configure()
	 */
	public function configure()
	{
		parent::configure();
		
		$this->tempXmlPath = sys_get_temp_dir();
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYahooDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYahooDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYahooDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYahooDistributionJobProviderData");

		return $this->handleSubmit($data, $data->distributionProfile, $data->providerData);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYahooDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYahooDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYahooDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYahooDistributionJobProviderData");
		
		return $this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYahooDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYahooDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYahooDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYahooDistributionJobProviderData");
		
		return $this->handleSubmit($data, $data->distributionProfile, $data->providerData);
	
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		
	}

	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYahooDistributionProfile $distributionProfile
	 * @param VidiunYahooDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunYahooDistributionProfile $distributionProfile, VidiunYahooDistributionJobProviderData $providerData)
	{
		$distributionProfile = $data->distributionProfile;
		$providerData = $data->providerData;
		$entryDistribution = $data->entryDistribution;

		$this->fieldValues = unserialize($providerData->fieldValues);
		if (!$this->fieldValues) {
			VidiunLog::err("fieldValues array is empty or null");
			throw new Exception("fieldValues array is empty or null");	
		}			
		//xml creation
		$flavorAssets = $this->getEntryDistributionFlavorAssets($entryDistribution);
		$thumbAssets = $this->getEntryDistributionThumbAssets($entryDistribution);
		$feed = new YahooDistributionFeedHelper(self::FEED_TEMPLATE, $distributionProfile, $providerData, $entryDistribution, $flavorAssets);		
		$feed->setFieldsForSubmit();		
		//create unique name for thumbnails 
		$currentTime = time();		
		$smallThumbDestFileName = $currentTime.'_'.basename($providerData->smallThumbPath); 
		$largeThumbDestFileName = $currentTime.'_'.basename($providerData->largeThumbPath);
		
		$feed->setThumbnailsPath($smallThumbDestFileName, $largeThumbDestFileName);		
		//create unique names for flavor assets 
		$feed->setStreams($flavorAssets, $currentTime);	
		$remoteId = $entryDistribution->entryId;
		$data->remoteId = $remoteId;
		$fileName = $remoteId .'_'. $currentTime . '.xml';
		$srcFile = $this->tempXmlPath . '/' . $fileName;
		$path = $distributionProfile->ftpPath;
		$destFile = "{$path}/{$fileName}";
			
		$xmlString = $feed->getXmlString();	
		file_put_contents($srcFile, $xmlString);	
		VidiunLog::info("XML written to file [$srcFile]");
		//upload file to FTP

		$ftpManager = $this->getFTPManager($distributionProfile);
		if(!$ftpManager)
			throw new Exception("FTP manager not loaded");
		
		//upload flavors and thumbnails to FTP
		$this->uploadThumbAssetsFiles($path, $providerData, $ftpManager, $smallThumbDestFileName, $largeThumbDestFileName);				
		$this->uploadFlavorAssetsFiles($path, $feed, $providerData, $ftpManager, $flavorAssets, $currentTime);	
		//upload feed to FTP
		$ftpManager->putFile($destFile, $srcFile, true);

		$data->sentData = $xmlString;
		$data->results = 'none'; 
		// the url should be accessed automatically
		if ($distributionProfile->processFeed == VidiunYahooDistributionProcessFeedActionStatus::AUTOMATIC)
		{
			$accessUrlResult = $this->accessUrl($distributionProfile, $destFile);
			$data->results = $accessUrlResult['result'];
		}			
		return true;			
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYahooDistributionProfile $distributionProfile
	 * @param VidiunYahooDistributionJobProviderData $providerData
	 */
	protected function handleDelete(VidiunDistributionJobData $data, VidiunYahooDistributionProfile $distributionProfile, VidiunYahooDistributionJobProviderData $providerData)
	{	
		$this->fieldValues = unserialize($providerData->fieldValues);
		if (!$this->fieldValues) {
			VidiunLog::err("fieldValues array is empty or null");
			throw new Exception("fieldValues array is empty or null");	
		}		
		$entryDistribution = $data->entryDistribution;	
		$feed = new YahooDistributionFeedHelper(self::DELETE_FEED_TEMPLATE, $distributionProfile, $providerData, $entryDistribution, null);			
		//create a feed with expiration time set to yesterday
		$feed->setFieldsForDelete();
		$currentTime = time();
	
		$remoteId = $entryDistribution->entryId;
		$data->remoteId = $remoteId;			
		$fileName = $remoteId .'_'. $currentTime. '.xml';
		$srcFile = $this->tempXmlPath . '/' . $fileName;
		$path = $distributionProfile->ftpPath;
		$destFile = "{$path}/{$fileName}";
				
		$ftpManager = $this->getFTPManager($distributionProfile);
		if(!$ftpManager)
			throw new Exception("FTP manager not loaded");
	
		$xmlString = $feed->getXmlString();
		file_put_contents($srcFile, $xmlString);
		VidiunLog::info("XML written to file [$srcFile]");
		//upload file to FTP
		$ftpManager = $this->getFTPManager($distributionProfile);
		$ftpManager->putFile($destFile, $srcFile, true);

		$data->sentData = $xmlString;
		$data->results = 'none'; 
		// the url should be accessed automatically
		if ($distributionProfile->processFeed == VidiunYahooDistributionProcessFeedActionStatus::AUTOMATIC)
		{
			$accessUrlResult = $this->accessUrl($distributionProfile, $destFile);
			$data->results = $accessUrlResult['result'];
		}				
		return true;		
	}
				
	/**
	 * 
	 * @param VidiunYahooDistributionProfile $distributionProfile
	 * @return ftpMgr
	 */
	protected function getFTPManager(VidiunYahooDistributionProfile $distributionProfile)
	{
		$ftpHost = $distributionProfile->ftpHost;
		$ftpUsername = $distributionProfile->ftpUsername;
		$ftpPassword = $distributionProfile->ftpPassword;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$engineOptions['passiveMode'] = true;
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		if(!$ftpManager){
			throw new Exception("FTP manager not loaded");
		}
		$ftpManager->login($ftpHost, $ftpUsername, $ftpPassword);
		return $ftpManager;
	}
	
	protected function getEntryDistributionFlavorAssets(VidiunEntryDistribution $entryDistribution)
	{
		$flavorAssetIds = $entryDistribution->flavorAssetIds;
		$partnerId = $entryDistribution->partnerId;
		
		return parent::getFlavorAssets($partnerId, $flavorAssetIds);
	}

	protected function getEntryDistributionThumbAssets(VidiunEntryDistribution $entryDistribution)
	{
		$thumbAssetIds = $entryDistribution->thumbAssetIds;
		$partnerId = $entryDistribution->partnerId;
		
		return parent::getThumbAssets($partnerId, $thumbAssetIds);
	}
	
	/**
	 * upload all flavor assets files to FTP
	 * @param VidiunYahooDistributionJobProviderData $providerData
	 * @param YahooDistributionFeedHelper $feed
	 */
	protected function uploadFlavorAssetsFiles($path, $feed, $providerData, $ftpManager, $flavorAssets, $currentTime)
	{
		/* @var $feed YahooDistributionFeedHelper */
		/* @var $providerData VidiunYahooDistributionJobProviderData */
		foreach ($flavorAssets as $asset)
		{
			$destName = $feed->flavorAssetUniqueName($asset, $currentTime);
			$destName = "{$path}/{$destName}";
			$videoAssetFilePathArray = unserialize($providerData->videoAssetFilePath);
			$sourceName = $videoAssetFilePathArray[$asset->id];
			$ftpManager->putFile($destName, $sourceName, true);
		}
	}
		
	protected function uploadThumbAssetsFiles($path, $providerData, $ftpManager, $smallThumbDestFileName, $largeThumbDestFileName)
	{			
			$smallThumbDestFileName = "{$path}/{$smallThumbDestFileName}";
			$largeThumbDestFileName	= "{$path}/{$largeThumbDestFileName}";			
			$ftpManager->putFile($smallThumbDestFileName, $providerData->smallThumbPath, true);
			$ftpManager->putFile($largeThumbDestFileName, $providerData->largeThumbPath, true);
	}
	
	/**
	 * access url.
	 * @param VidiunYahooDistributionProfile $distributionProfile
	 */
	private function accessUrl(VidiunYahooDistributionProfile $distributionProfile, $fileName)
	{
		$url = self::ACCESS_URL;
		$params = array(
			'UserName' => $distributionProfile->ftpUsername,
			'Password' => $distributionProfile->ftpPassword,
			'FileName' => $fileName,
		);
		$url = $url.'?'.http_build_query($params);
		
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		$result = curl_exec($ch);		
		$resultsArr = array(
              'result' => $result,
              'error' => curl_error($ch),
              'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );  		
		
        curl_close($ch);
		return $resultsArr;
	}
	
}