<?php
/**
 * @package plugins.ftpDistribution
 * @subpackage lib
 */
class FtpDistributionEngine extends PublicPrivateKeysDistributionEngine implements
	IDistributionEngineSubmit,
	IDistributionEngineUpdate,
	IDistributionEngineDelete
{
	protected $tempFilePath;
	
	const TEMP_DIRECTORY = 'ftp_distribution';
	
	/**
	 * @see IDistributionEngine::configure()
	 */
	public function configure()
	{
		parent::configure();
		
		if(VBatchBase::$taskConfig->params->tempFilePath)
		{
			$this->tempFilePath = VBatchBase::$taskConfig->params->tempFilePath;
			if(!is_dir($this->tempFilePath))
				vFile::fullMkfileDir($this->tempFilePath, 0777, true);
		}
		else
		{
			$this->tempFilePath = sys_get_temp_dir();
			VidiunLog::info('params.tempFilePath configuration not supplied, using default system directory ['.$this->tempFilePath.']');
		}
	}
	
	/**
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		return $this->submitOrUpdate($data);
	}
	
	/**
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		return $this->submitOrUpdate($data);
	}
	
	/**
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		$this->validateObjects($data);
		
		$fileManager = $this->getFileTransferManager($data->distributionProfile);
		VidiunLog::info('Using '.get_class($fileManager).' file transfer manager');
		
		foreach($data->mediaFiles as $remoteFile)
		{
			/* @var $remoteFile VidiunDistributionRemoteMediaFile */
			VidiunLog::info('Trying to delete file ['.$remoteFile->remoteId.'], version ['.$remoteFile->version.'] for asset id ['.$remoteFile->assetId.']');
			try
			{
				$fileManager->delFile($remoteFile->remoteId);
			}
			catch(Exception $ex)
			{
				VidiunLog::err($ex);
				VidiunLog::err('Failed to delete file ['.$remoteFile->remoteId.']');
			}
		}
		
		return true;
	}
	
	public function submitOrUpdate(VidiunDistributionJobData $data)
	{
		$this->validateObjects($data);
		
		$fileManager = $this->getFileTransferManager($data->distributionProfile);
		VidiunLog::info('Using '.get_class($fileManager).' file transfer manager');
		
		/* @var $providerData VidiunFtpDistributionJobProviderData */
		$providerData = $data->providerData;
		
		/* @var $distributionProfile VidiunFtpDistributionProfile */
		$distributionProfile = $data->distributionProfile;
		
		if (!is_array($providerData->filesForDistribution) || count($providerData->filesForDistribution) == 0)
			throw new Exception('No files to distribute');
			
		if (!$data->mediaFiles)
			$data->mediaFiles = array();
			
		$this->syncFiles($fileManager, $data->mediaFiles, $providerData->filesForDistribution, $distributionProfile);

        $this->storeMetadataFileAsSentData($data, $providerData->filesForDistribution);
		return true;
	}
	
	public function syncFiles(vFileTransferMgr $fileManager, &$remoteFiles, $filesForDistribution, VidiunFtpDistributionProfile $distributionProfile)
	{
		foreach($filesForDistribution as $file)
		{
			/* @var $file VidiunFtpDistributionFile */
			if ($file->assetId == 'metadata')
			{
				$newestRemoteFile = $this->getNewestRemoteFileById($remoteFiles, 'metadata');
				if (is_null($newestRemoteFile))
				{
					$file->version = 0;
				}
				else
				{
					$versionAndHash = explode('_', $newestRemoteFile->version);
					$newestRemoteFileVersion = $versionAndHash[0];
					$newestRemoteFileHash = $versionAndHash[1];
					
					// newest metadata file has the same hash as the one we want to distribute, ignore it
					if ($file->hash === $newestRemoteFileHash)
					{
						continue;
					}
					// hash was modified and it's a new metadata file, modify the file name to include the version
					else
					{
						$file->version = $newestRemoteFileVersion + 1;
						$filename = $file->filename;
						$file->filename = pathinfo($filename, PATHINFO_FILENAME) . '_v' . $file->version . '.' . pathinfo($filename, PATHINFO_EXTENSION);
					}
				}
			}
			else
			{
				$newestRemoteFile = $this->getNewestRemoteFileById($remoteFiles, $file->assetId);
				
				if (!is_null($newestRemoteFile))
				{
					// same vesion, ignore it
					if (version_compare($newestRemoteFile->version, $file->version, '='))
						continue;
						
					// version was incremented, modify the file name to include the version
					if (version_compare($file->version, $newestRemoteFile->version, '>'))
					{
						$filename = $file->filename;
						$file->filename = pathinfo($filename, PATHINFO_FILENAME) . '_v' . $file->version . '.' . pathinfo($filename, PATHINFO_EXTENSION);
					}
				}
			}
			$remoteFile = $this->distributeFile($fileManager, $file, $distributionProfile);
			if ($remoteFile)
				$remoteFiles[] = $remoteFile;
		}
	}
	
	
	/**
	 * @param vFileTransferMgr $fileManager
	 * @param VidiunFtpDistributionFile $file
	 * @param VidiunFtpDistributionProfile $distributionProfile
	 * @return VidiunDistributionRemoteMediaFile
	 */
	protected function distributeFile(vFileTransferMgr $fileManager, VidiunFtpDistributionFile $file, VidiunFtpDistributionProfile $distributionProfile)
	{
		$remoteFilePath = $this->cleanPath($distributionProfile->basePath . '/' . $file->filename);

		if ($file->contents)
		{
			$filename = uniqid(null, true) . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);
			$tempFilePath = $this->tempFilePath . '/' . $filename;
			file_put_contents($tempFilePath, $file->contents);
			VidiunLog::info('Sending contents, using temp path [' . $tempFilePath . ']');
		}
		else
		{
			$tempFilePath = $this->getAssetFile($file->assetId, $this->tempDirectory);
			if (!$tempFilePath)
				return null;
			VidiunLog::info('Sending local file [' . $tempFilePath . ']');
		}

		$fileManager->putFile($remoteFilePath, $tempFilePath);
		unlink($tempFilePath);

		$remoteFile = new VidiunDistributionRemoteMediaFile();
		if ($file->hash)
			$remoteFile->version = $file->version . '_' . $file->hash;
		else
			$remoteFile->version = $file->version;
		$remoteFile->assetId = $file->assetId;
		$remoteFile->remoteId = $remoteFilePath; // remote id is the file path, later it will be used to delete the distributed files
		return $remoteFile;
	}

	protected function cleanPath($path)
	{
		$path = trim($path);
		$path = str_replace('\\', '/', $path);
		$path = preg_replace('/\/+/', '/', $path);
		return $path;
	}
	
	protected function getNewestRemoteFileById($remoteFiles, $id) 
	{
		$newestRemoteFile = null;
		foreach($remoteFiles as $remoteFile)
		{
			/* @var $remoteFile VidiunDistributionRemoteMediaFile */
			if ($remoteFile->assetId === $id)
			{
				if (is_null($newestRemoteFile)) {
					$newestRemoteFile = $remoteFile;
					continue;
				}
					
				if (version_compare($remoteFile->version, $newestRemoteFile->version, '>'))
					$newestRemoteFile = $remoteFile;
			}
		}
		return $newestRemoteFile;
	}
	
	protected function getRemoteMetadataFiles($remoteFiles) 
	{
		$remoteFiles = array();
		foreach($remoteFiles as $remoteFile)
		{
			/* @var $remoteFile VidiunDistributionRemoteMediaFile */
			if ($remoteFile->assetId === 'metadata')
				$remoteFiles[] = $remoteFile;
		}
		return $remoteFiles;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 */
	protected function validateObjects(VidiunDistributionJobData $data)
	{
		if(!$data->distributionProfile instanceof VidiunFtpDistributionProfile)
			throw new Exception('Distribution profile must be of type VidiunFtpDistributionProfile');
	
		if (!$data->providerData instanceof VidiunFtpDistributionJobProviderData)
			throw new Exception('Provider data must be of type VidiunFtpDistributionJobProviderData');
	}
	
	/**
	 * 
	 * @param VidiunFtpDistributionProfile $distributionProfile
	 * @return vFileTransferMgr
	 */
	protected function getFileTransferManager(VidiunFtpDistributionProfile $distributionProfile)
	{
		$host = $distributionProfile->host;
		$port = $distributionProfile->port;
		$protocol = $distributionProfile->protocol;
		$username = $distributionProfile->username;
		$password = $distributionProfile->password;
		if ($protocol == VidiunDistributionProtocol::ASPERA)
		{
			$publicKey = $distributionProfile->asperaPublicKey;
        	$privateKey = $distributionProfile->asperaPrivateKey;
		}
		else 
		{
			$publicKey = $distributionProfile->sftpPublicKey;
        	$privateKey = $distributionProfile->sftpPrivateKey;
		}
        
        $passphrase = $distributionProfile->passphrase ? $distributionProfile->passphrase : null;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$fileTransferManager = vFileTransferMgr::getInstance($protocol, $engineOptions);
        if (trim($privateKey))
        {
            try
            {
            	$publicKeyTempPath = $this->getFileLocationForSFTPKey($distributionProfile->id, $publicKey, 'publickey');
            	$privateKeyTempPath = $this->getFileLocationForSFTPKey($distributionProfile->id, $privateKey, 'privatekey');
                $fileTransferManager->loginPubKey($host, $username, $publicKeyTempPath, $privateKeyTempPath, $passphrase, ($port) ? $port : null);
            }
            catch(Exception $ex)
            {
                if (file_exists($publicKeyTempPath))
                    unlink($publicKeyTempPath);
                if (file_exists($privateKeyTempPath))
                    unlink($privateKeyTempPath);
                throw $ex;
            }
        }
        else
        {
            $fileTransferManager->login($host, $username, $password, ($port) ? $port : null);
        }
		return $fileTransferManager;
	}

    private function storeMetadataFileAsSentData(VidiunDistributionJobData $data, $filesForDistribution)
    {
        if (is_array($filesForDistribution))
        {
            foreach($filesForDistribution as $file)
            {
                /* @var $file VidiunFtpDistributionFile */
                if ($file->assetId == 'metadata')
                {
                    $data->sentData = $file->contents;
                }
            }
        }
    }

	public function getTempDirectory()
	{
		return self::TEMP_DIRECTORY;
	}
    
}