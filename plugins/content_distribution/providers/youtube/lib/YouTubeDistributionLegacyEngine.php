<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage lib
 */
class YouTubeDistributionLegacyEngine extends PublicPrivateKeysDistributionEngine implements
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineReport,
	IDistributionEngineDelete,
	IDistributionEngineCloseUpdate,
	IDistributionEngineCloseSubmit,
	IDistributionEngineCloseDelete
{
	const TEMP_DIRECTORY = 'youtube_distribution';
	const FEED_TEMPLATE = 'feed_template.xml';

	/**
	 * @var sftpMgr
	 */
	protected $_sftpManager;

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYouTubeDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYouTubeDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYouTubeDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYouTubeDistributionJobProviderData");
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		$statusXml = $this->fetchStatusXml($data, $data->distributionProfile, $data->providerData);

		if ($statusXml === false) // no status yet
		{
			// try to get batch status xml to see if there is an internal error on youtube's batch
			$batchStatus = $this->fetchBatchStatus($data, $data->distributionProfile, $data->providerData);
			if ($batchStatus)
				throw new Exception('Internal failure on YouTube, internal_failure-status.xml was found. Error ['.$batchStatus.']');

			return false;
		}
			
		$statusParser = new YouTubeDistributionLegacyStatusParser($statusXml);
		$status = $statusParser->getStatusForCommand('Insert');
		$statusDetail = $statusParser->getStatusDetailForCommand('Insert');
		if (is_null($status))
		{
			// try to get the status of Parse command
			$status = $statusParser->getStatusForCommand('Parse');
			$statusDetail = $statusParser->getStatusDetailForCommand('Parse');
			if (!is_null($status))
				throw new Exception('Distribution failed on parsing command with status ['.$status.'] and error ['.$statusDetail.']');
			else
				throw new Exception('Status could not be found after distribution submission');
		}
		
		if ($status != 'Success')
			throw new Exception('Distribution failed with status ['.$status.'] and error ['.$statusDetail.']');
			
		$remoteId = $statusParser->getRemoteId();
		if (is_null($remoteId))
			throw new Exception('Remote id was not found after distribution submission');
		
		$data->remoteId = $remoteId;
			
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYouTubeDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYouTubeDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYouTubeDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYouTubeDistributionJobProviderData");
		
		$this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		$statusXml = $this->fetchStatusXml($data, $data->distributionProfile, $data->providerData);

		if ($statusXml === false) // no status yet
			return false;
			
		$statusParser = new YouTubeDistributionLegacyStatusParser($statusXml);
		$status = $statusParser->getStatusForCommand('Delete');
		$statusDetail = $statusParser->getStatusDetailForCommand('Delete');
		if (is_null($status))
			throw new Exception('Status could not be found after deletion request');
		
		if ($status != 'Success')
			throw new Exception('Delete failed with status ['.$status.'] and error ['.$statusDetail.']');
			
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunYouTubeDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunYouTubeDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunYouTubeDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunYouTubeDistributionJobProviderData");
		
		$this->handleUpdate($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		$statusXml = $this->fetchStatusXml($data, $data->distributionProfile, $data->providerData);

		if ($statusXml === false) // no status yet
			return false;
			
		$statusParser = new YouTubeDistributionLegacyStatusParser($statusXml);
		$status = $statusParser->getStatusForCommand('Update');
		$statusDetail = $statusParser->getStatusDetailForCommand('Update');
		if (is_null($status))
			throw new Exception('Status could not be found after distribution update');
		
		if ($status != 'Success')
			throw new Exception('Update failed with status ['.$status.'] and error ['.$statusDetail.']');
			
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineReport::fetchReport()
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data)
	{
		return false;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @param VidiunYouTubeDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunYouTubeDistributionProfile $distributionProfile, VidiunYouTubeDistributionJobProviderData $providerData)
	{
		$entryId = $data->entryDistribution->entryId;
		$entry = $this->getEntry($data->entryDistribution->partnerId, $entryId);

		$videoFilePath = $providerData->videoAssetFilePath;
		if (!$videoFilePath)
			throw new VidiunDistributionException('No video asset to distribute, the job will fail');

		if (!file_exists($videoFilePath))
			throw new VidiunDistributionException('The file ['.$videoFilePath.'] was not found (probably not synced yet), the job will retry');
			
		$thumbnailFilePath = $providerData->thumbAssetFilePath;
		
		$feed = new YouTubeDistributionLegacyFeedHelper(self::FEED_TEMPLATE, $distributionProfile, $providerData);
		$feed->setAction('Insert');
		$feed->setMetadataFromEntry();
		$newPlaylists = $feed->setPlaylists($providerData->currentPlaylists);
		$feed->setContentUrl('file://' . pathinfo($videoFilePath, PATHINFO_BASENAME));
		if (file_exists($thumbnailFilePath))
			$feed->setThumbnailUrl('file://' . pathinfo($thumbnailFilePath, PATHINFO_BASENAME));
		$feed->setAdParams();
			
		$sftpManager = $this->getSFTPManager($distributionProfile);
		
		$feed->sendFeed($sftpManager);
		$data->sentData = $feed->getXml();
		$data->results = 'none'; // otherwise vContentDistributionFlowManager won't save sentData
		
		// upload the video
		$videoSFTPPath = $feed->getDirectoryName() . '/' . pathinfo($videoFilePath, PATHINFO_BASENAME);
		$sftpManager->putFile($videoSFTPPath, $videoFilePath);
		
		// upload the thumbnail if exists
		if (file_exists($thumbnailFilePath))
		{
			$thumbnailSFTPPath = $feed->getDirectoryName() . '/' . pathinfo($thumbnailFilePath, PATHINFO_BASENAME);
			$sftpManager->putFile($thumbnailSFTPPath, $thumbnailFilePath);
		}
		
		$feed->setDeliveryComplete($sftpManager);
		
		$providerData->sftpDirectory = $feed->getDirectoryName();
		$providerData->sftpMetadataFilename = $feed->getMetadataTempFileName();
		$providerData->currentPlaylists = $newPlaylists;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @param VidiunYouTubeDistributionJobProviderData $providerData
	 */
	protected function handleDelete(VidiunDistributionJobData $data, VidiunYouTubeDistributionProfile $distributionProfile, VidiunYouTubeDistributionJobProviderData $providerData)
	{
		$feed = new YouTubeDistributionLegacyFeedHelper(self::FEED_TEMPLATE, $distributionProfile, $providerData);
		$feed->setAction('Delete');
		$feed->setVideoId($data->remoteId);
		$feed->setDistributionRestrictionRule(""); //to update <yt:distribution_restriction> field 
		
		$sftpManager = $this->getSFTPManager($distributionProfile);
		
		$feed->sendFeed($sftpManager);
		$data->sentData = $feed->getXml();
		$data->results = 'none'; // otherwise vContentDistributionFlowManager won't save sentData
		$feed->setDeliveryComplete($sftpManager);
		
		$providerData->sftpDirectory = $feed->getDirectoryName();
		$providerData->sftpMetadataFilename = $feed->getMetadataTempFileName();
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @param VidiunYouTubeDistributionJobProviderData $providerData
	 */
	protected function handleUpdate(VidiunDistributionJobData $data, VidiunYouTubeDistributionProfile $distributionProfile, VidiunYouTubeDistributionJobProviderData $providerData)
	{
		$entryId = $data->entryDistribution->entryId;
		$entry = $this->getEntry($data->entryDistribution->partnerId, $entryId);

		$feed = new YouTubeDistributionLegacyFeedHelper(self::FEED_TEMPLATE, $distributionProfile, $providerData);
		$feed->setAction('Update');
		$feed->setVideoId($data->remoteId);
		$feed->setMetadataFromEntry();
		$newPlaylists = $feed->setPlaylists($providerData->currentPlaylists);
		$feed->setAdParams();
		
		$thumbnailFilePath = $providerData->thumbAssetFilePath;
		if (file_exists($thumbnailFilePath))
			$feed->setThumbnailUrl('file://' . pathinfo($thumbnailFilePath, PATHINFO_BASENAME));
		
		$sftpManager = $this->getSFTPManager($distributionProfile);
			
		if (file_exists($thumbnailFilePath))
		{
			$thumbnailSFTPPath = $feed->getDirectoryName() . '/' . pathinfo($thumbnailFilePath, PATHINFO_BASENAME);
			$sftpManager->putFile($thumbnailSFTPPath, $thumbnailFilePath);
		}
		
		$feed->sendFeed($sftpManager);
		$data->sentData = $feed->getXml();
		$data->results = 'none'; // otherwise vContentDistributionFlowManager won't save sentData
		$feed->setDeliveryComplete($sftpManager);
		
		$providerData->sftpDirectory = $feed->getDirectoryName();
		$providerData->sftpMetadataFilename = $feed->getMetadataTempFileName();
		$providerData->currentPlaylists = $newPlaylists;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @param VidiunYouTubeDistributionJobProviderData $providerData
	 * @return Status XML or FALSE when status is not available yet
	 */
	protected function fetchStatusXml(VidiunDistributionJobData $data, VidiunYouTubeDistributionProfile $distributionProfile, VidiunYouTubeDistributionJobProviderData $providerData)
	{
		$statusFilePath = $providerData->sftpDirectory . '/' . 'status-' . $providerData->sftpMetadataFilename;
		$sftpManager = $this->getSFTPManager($distributionProfile);
		$statusXml = null;
		try
		{
			VidiunLog::info('Trying to get the following status file: ['.$statusFilePath.']');
			$statusXml = $sftpManager->getFile($statusFilePath);
		}
		catch(vFileTransferMgrException $ex) // file is still missing
		{
			VidiunLog::info('File doesn\'t exist yet, retry later');
			return false;
		}

		VidiunLog::info("Status file was found [$statusXml]");

		$data->results = $statusXml;
		return $statusXml;
	}

	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @param VidiunYouTubeDistributionJobProviderData $providerData
	 * @return string Status XML or FALSE when status is not available yet
	 */
	protected function fetchBatchStatus(VidiunDistributionJobData $data, VidiunYouTubeDistributionProfile $distributionProfile, VidiunYouTubeDistributionJobProviderData $providerData)
	{
		$statusFilePath = $providerData->sftpDirectory . '/internal_failure-status.xml';
		$sftpManager = $this->getSFTPManager($distributionProfile);
		$statusXml = null;
		try
		{
			VidiunLog::info('Trying to get the following status file: ['.$statusFilePath.']');
			$statusXml = $sftpManager->getFile($statusFilePath);
			VidiunLog::info("Status file was found [$statusXml]");
			return $statusXml;
		}
		catch(vFileTransferMgrException $ex) // file is still missing
		{
			VidiunLog::info('File doesn\'t exist yet, so no internal failure was found till now');
			return false;
		}
	}
	
	/**
	 * 
	 * @param VidiunYouTubeDistributionProfile $distributionProfile
	 * @return sftpMgr
	 */
	protected function getSFTPManager(VidiunYouTubeDistributionProfile $distributionProfile)
	{
		if (!is_null($this->_sftpManager))
			return $this->_sftpManager;

		$serverUrl = $distributionProfile->sftpHost;
		$loginName = $distributionProfile->sftpLogin;
		$publicKeyFile = $this->getFileLocationForSFTPKey($distributionProfile->id, $distributionProfile->sftpPublicKey, 'publickey');
		$privateKeyFile = $this->getFileLocationForSFTPKey($distributionProfile->id, $distributionProfile->sftpPrivateKey, 'privatekey');
		$port = 22;
		if ($distributionProfile->sftpPort)
			$port = $distributionProfile->sftpPort;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$sftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP, $engineOptions);
		$sftpManager->loginPubKey($serverUrl, $loginName, $publicKeyFile, $privateKeyFile, null, $port);
		$this->_sftpManager = $sftpManager;
		return $this->_sftpManager;
	}

	public function getTempDirectory()
	{
		return self::TEMP_DIRECTORY;
	}
}