<?php
class VFileTransferExportEngine extends VExportEngine
{
	protected $srcFile;
	
	protected $destFile;
	
	protected $protocol;

	protected $encryptionKey;
	
	/* (non-PHPdoc)
	 * @see VExportEngine::init()
	 */
	function __construct($data, $jobSubType) {
		parent::__construct($data);
		
		$this->protocol = $jobSubType;
		$this->srcFile = str_replace('//', '/', trim($this->data->srcFileSyncLocalPath));
		$this->destFile = str_replace('//', '/', trim($this->data->destFileSyncStoredPath));
		$this->encryptionKey = $this->data->srcFileEncryptionKey;
	}
	
	/* (non-PHPdoc)
	 * @see VExportEngine::export()
	 */
	function export() 
	{
		if(!VBatchBase::pollingFileExists($this->srcFile))
			throw new vTemporaryException("Source file {$this->srcFile} does not exist");
							
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$engineOptions['passiveMode'] = $this->data->ftpPassiveMode;
		$engineOptions['createLink'] = $this->data->createLink;
		if($this->data instanceof VidiunAmazonS3StorageExportJobData)
		{
			$engineOptions['filesAcl'] = $this->data->filesPermissionInS3;
			$engineOptions['s3Region'] = $this->data->s3Region;
			$engineOptions['sseType'] = $this->data->sseType;
			$engineOptions['sseVmsKeyId'] = $this->data->sseVmsKeyId;
			$engineOptions['signatureType'] = $this->data->signatureType;
			$engineOptions['endPoint'] = $this->data->endPoint;
		}
			
		$engine = vFileTransferMgr::getInstance($this->protocol, $engineOptions);
		
		try
		{
			$keyPairLogin = false;
			if($this->protocol == VidiunStorageProfileProtocol::SFTP) {
				$keyPairLogin = ($this->data->serverPrivateKey || $this->data->serverPublicKey);
			}
			
			if($keyPairLogin) {
				$privateKeyFile = $this->data->serverPrivateKey ? vFile::createTempFile($this->data->serverPrivateKey, 'privateKey', 0600) : null;
				$publicKeyFile = $this->data->serverPublicKey ? vFile::createTempFile($this->data->serverPublicKey, 'publicKey', 0600) : null;
				$engine->loginPubKey($this->data->serverUrl, $this->data->serverUsername, $publicKeyFile, $privateKeyFile, $this->data->serverPassPhrase);
			} else {	
				$engine->login($this->data->serverUrl, $this->data->serverUsername, $this->data->serverPassword);
			}
		}
		catch(Exception $e)
		{
			throw new vTemporaryException($e->getMessage());
		}
	
		try
		{
			if (is_file($this->srcFile))
			{
				$this->putFile($engine, $this->destFile, $this->srcFile, $this->data->force);
			}
			else if (is_dir($this->srcFile))
			{
				$filesPaths = vFile::dirList($this->srcFile);
				$destDir = $this->destFile;
				foreach ($filesPaths as $filePath)
				{
					$destFile = $destDir . '/' . basename($filePath);
					$this->putFile($engine, $destFile, $filePath, $this->data->force);
				}
			}
		}
		catch(vFileTransferMgrException $e)
		{
			if($e->getCode() == vFileTransferMgrException::remoteFileExists)
				throw new vApplicativeException(VidiunBatchJobAppErrors::FILE_ALREADY_EXISTS, $e->getMessage());
			
			throw new Exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see VExportEngine::verifyExportedResource()
	 */
	function verifyExportedResource() {
		// TODO Auto-generated method stub
		
	}
    
    /* (non-PHPdoc)
     * @see VExportEngine::delete()
     */
    function delete()
    {
        $engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
        $engineOptions['passiveMode'] = $this->data->ftpPassiveMode;
        $engine = vFileTransferMgr::getInstance($this->protocol, $engineOptions);
        
        try{
            $engine->login($this->data->serverUrl, $this->data->serverUsername, $this->data->serverPassword);
            $engine->delFile($this->destFile);
        }
        catch(vFileTransferMgrException $ke)
        {
            throw new vApplicativeException($ke->getCode(), $ke->getMessage());
        }
        
        return true;
    }

	private function putFile(vFileTransferMgr $engine, $destFilePath, $srcFilePath, $force)
	{
		if (!$this->encryptionKey)
			$engine->putFile($destFilePath, $srcFilePath, $force);
		else
		{
			$tempPath = VBatchBase::createTempClearFile($srcFilePath, $this->encryptionKey);
			$engine->putFile($destFilePath, $tempPath, $force);
			unlink($tempPath);
		}
		if(VBatchBase::$taskConfig->params->chmod)
		{
			try {
				$engine->chmod($destFilePath, VBatchBase::$taskConfig->params->chmod);
			}
			catch(Exception $e){}
		}
	}
}