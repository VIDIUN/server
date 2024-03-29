<?php
/**
 * @package plugins.huluDistribution
 * @subpackage lib
 */
class HuluDistributionEngine extends DistributionEngine implements 
	IDistributionEngineSubmit,
	IDistributionEngineCloseSubmit
{
	
	
	protected $tempFilePath;
	
	const TEMP_DIRECTORY = 'hulu_distribution';
	
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 * 
	 * Demonstrate asynchronous external API usage
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		// validates received object types
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunHuluDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunHuluDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunHuluDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunHuluDistributionJobProviderData");
		
		// call the actual submit action
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		$entryId = $data->entryDistribution->entryId;
		$loginName = $data->distributionProfile->sftpLogin;
		$loginPass = $data->distributionProfile->sftpPass;
		
		return true;
	}

	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunHuluDistributionProfile $distributionProfile
	 * @param VidiunHuluDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunHuluDistributionProfile $distributionProfile, VidiunHuluDistributionJobProviderData $providerData)
	{
		$feed = new HuluFeedHelper('hulu_template.xml', $distributionProfile, $providerData);
		$xml = $feed->getXml();
		
		$videoFilePath = $providerData->videoAssetFilePath;
		$thumbAssetFilePath = $providerData->thumbAssetFilePath;
		$captionsFilesPaths = $providerData->captionLocalPaths;
		$protocol = $distributionProfile->protocol ? $distributionProfile->protocol : VidiunDistributionProtocol::SFTP;
		
		$remoteVideoFileName = $providerData->fileBaseName.'.'.pathinfo($videoFilePath, PATHINFO_EXTENSION);
		$remoteThumbFileName = $providerData->fileBaseName.'.'.pathinfo($thumbAssetFilePath, PATHINFO_EXTENSION);
		$remoteXmlFileName = $providerData->fileBaseName.'.xml';
		switch ($protocol){
			case VidiunDistributionProtocol::SFTP:
				$sftpBasePath = '/home/' . $distributionProfile->sftpLogin . '/upload';
				$videoSFTPPath = $sftpBasePath.'/'.$remoteVideoFileName;
				$thumbSFTPPath = $sftpBasePath.'/'.$remoteThumbFileName;
				$xmlSFTPPath = $sftpBasePath.'/'.$remoteXmlFileName;
				VidiunLog::info('$videoSFTPPath:' . $videoSFTPPath);
				VidiunLog::info('$thumbSFTPPath:' . $thumbSFTPPath);
				VidiunLog::info('$xmlSFTPPath:' . $xmlSFTPPath);
				VidiunLog::info('XML:' . $xml);
				$fileManager = $this->getSFTPManager($distributionProfile);
				$fileManager->putFile($videoSFTPPath, $videoFilePath);
				if($thumbAssetFilePath && file_exists($thumbAssetFilePath))
					$fileManager->putFile($thumbSFTPPath, $thumbAssetFilePath);
					
				foreach ($captionsFilesPaths as $captionFilePath){
					if(file_exists($captionFilePath->value)){
						$remoteCaptionFileName = $providerData->fileBaseName.'.'.pathinfo($captionFilePath->value, PATHINFO_EXTENSION);
						$captionSFTPPath = $sftpBasePath.'/'.$remoteCaptionFileName;
						VidiunLog::info('$captionSFTPPath:' . $captionSFTPPath);
						$fileManager->putFile($captionSFTPPath, $captionFilePath->value);
					}
				}
				$fileManager->filePutContents($xmlSFTPPath, $xml);
				break;
			case VidiunDistributionProtocol::ASPERA:
				$xmlTempPath = $this->getFileLocation($distributionProfile->id, $xml, $providerData->fileBaseName.'.xml');
				$host = $distributionProfile->asperaHost;
				$username = $distributionProfile->asperaLogin;
				$password = $distributionProfile->asperaPass;
				$privateKey = $distributionProfile->asperaPrivateKey;
				$passphrase = $distributionProfile->passphrase;
				$port = $distributionProfile->port;
				$privateKeyTempPath = null;
				if (trim($privateKey)){
					$privateKeyTempPath = $this->getFileLocation($distributionProfile->id, $privateKey, 'privatekey');
				}
				if ($videoFilePath && file_exists($videoFilePath))
					$this->uploadFileWithAspera($host, $username, $videoFilePath, $password, $privateKeyTempPath, $passphrase, $port, $remoteVideoFileName);
				else{
					throw new vFileTransferMgrException("video file [$videoFilePath] not exists", vFileTransferMgrException::localFileNotExists);
				}
				if($thumbAssetFilePath && file_exists($thumbAssetFilePath))
					$this->uploadFileWithAspera($host, $username, $thumbAssetFilePath, $password, $privateKeyTempPath, $passphrase, $port, $remoteThumbFileName );
				foreach ($captionsFilesPaths as $captionFilePath){
					if(file_exists($captionFilePath->value)){
						$remoteCaptionFileName = $providerData->fileBaseName.'.'.pathinfo($captionFilePath->value, PATHINFO_EXTENSION);
						$this->uploadFileWithAspera($host, $username, $captionFilePath->value, $password, $privateKeyTempPath, $passphrase, $port, $remoteCaptionFileName);
					}
				}
				if($xmlTempPath && file_exists($xmlTempPath))
					$this->uploadFileWithAspera($host, $username, $xmlTempPath, $password, $privateKeyTempPath, $passphrase, $port, $remoteXmlFileName);
				break;
		}
	}
	
	private function getFileLocation($distributionProfileId, $content, $fileName) 
	{
		$tempDirectory = $this->getTempDirectoryForProfile($distributionProfileId);
		$fileLocation = $tempDirectory . $fileName;
		if (!file_exists($fileLocation) || (file_get_contents($fileLocation) !== $content))
		{
			file_put_contents($fileLocation, $content);
			chmod($fileLocation, 0600);
		}
		
		return $fileLocation;
	}
	
	private function getTempDirectoryForProfile($distributionProfileId)
	{
		$tempFilePath = $this->tempDirectory . '/' . self::TEMP_DIRECTORY . '/' . $distributionProfileId . '/';
		if (!file_exists($tempFilePath))
			mkdir($tempFilePath, 0777, true);
		return $tempFilePath;
	}
	
	/**
	 * 
	 * @param VidiunHuluDistributionProfile $distributionProfile
	 * @return sftpMgr
	 */
	protected function getSFTPManager(VidiunHuluDistributionProfile $distributionProfile)
	{
		$serverUrl = $distributionProfile->sftpHost;
		$loginName = $distributionProfile->sftpLogin;
		$loginPass = $distributionProfile->sftpPass;
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$sftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::SFTP, $engineOptions);
		$sftpManager->login($serverUrl, $loginName, $loginPass);
		return $sftpManager;
	}
	
	private function uploadFileWithAspera($host, $username, $filePath, $password = null, $privateKeyTempPath = null, $passphrase = null, $port = 22, $remoteFilePath = ''){
		
		$this->validateParameters($host, $username, $filePath, $password, $privateKeyTempPath, $passphrase, $port, $remoteFilePath);
				
		$remoteFilePath = ltrim($remoteFilePath,'/');
		$cmd= $this->getCmdPrefix($privateKeyTempPath, $passphrase, $password, $port);
		$cmd.=" $filePath $username@$host:'$remoteFilePath'";
		$res = $this->executeCmd($cmd);
		if (!$res){
			$last_error = error_get_last();
			throw new VidiunDistributionException("Can't put file [$remoteFilePath] - " . $last_error['message'], vFileTransferMgrException::otherError);
		}
	}
	
	private function validateParameters($host, $username, $filePath, $password = null, $privateKeyTempPath = null, $passphrase = null, $port = 22, $remoteFilePath = '') {
		
		$VALID_HOSTNAME_PATTERN = "/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\\-]*[a-zA-Z0-9])\\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\\-]*[A-Za-z0-9])$/";
		$VALID_USERNAME_PATTERN = "/^([a-z_][a-z0-9_]{0,30})$/";
		
		$validInput = TRUE;
		$validInput &= (preg_match ($VALID_HOSTNAME_PATTERN, $host) === 1); // $host
		$validInput &= (preg_match ($VALID_USERNAME_PATTERN, $username) === 1); // $username
		// $filePath : No need in validation, Checked by the callee
		$validInput &= (is_null($password)) || (strpos($password, "'") === FALSE); // $password : can't contain ' 
		// $privateKeyTempPath: No need in validation, inner parameter
		$validInput &= (is_null($passphrase)) || (strpos($passphrase, "'") === FALSE);// $passphrase : can't contain '
		$validInput &= is_numeric($port); // $port 
		// $remoteFilePath : No need in validation, inner parameter with validation in creation.
		
		if(!$validInput)
			throw new vFileTransferMgrException("Can't put file, Illegal parameters");
	}
	
	private function getCmdPrefix($privateKeyTempPath, $passphrase, $password, $port){
		$cmd = '';
		if ($privateKeyTempPath){
			if ($passphrase)
				$cmd = "(echo '$passphrase') | ascp ";
			else  
				$cmd = "ascp ";
		}
		else 
			$cmd = "(echo '$password') | ascp ";
		$cmd.=" -P $port ";
		
		//when connecting to a remote host and prompted to accept a host key, ascp ignores the request
		$cmd.=" --ignore-host-key ";
		if ($privateKeyTempPath)
			$cmd.=" -i $privateKeyTempPath ";
		return $cmd;
		
	}
	
	private function executeCmd($cmd){
		VidiunLog::info('Executing command: '.$cmd);
		$return_value = null;
		$beginTime = time();
		system($cmd, $return_value);
		$duration = (time() - $beginTime)/1000;
		VidiunLog::info("Execution took [$duration]sec with value [$return_value]");
		if ($return_value == 0)
			return true;
		return false;
	}

}