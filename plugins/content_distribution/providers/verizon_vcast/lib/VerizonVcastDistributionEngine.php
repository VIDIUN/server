<?php
/**
 * @package plugins.verizonVcastDistribution
 * @subpackage lib
 */
class VerizonVcastDistributionEngine extends DistributionEngine implements 
	IDistributionEngineSubmit,
	IDistributionEngineCloseSubmit,
	IDistributionEngineUpdate,
	IDistributionEngineCloseUpdate,
	IDistributionEngineDelete,
	IDistributionEngineCloseDelete
{
	const VERIZON_STATUS_PUBLISHED = 'PUBLISHED';
	const VERIZON_STATUS_PENDING = 'PENDING';
	
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
		
		// verizon didn't approve that this logic does work, for now just mark every submited xml as successful
		return true;
		
		$publishState = $this->fetchStatus($data);
		switch($publishState)
		{
			case self::VERIZON_STATUS_PUBLISHED:
				return true;
			case self::VERIZON_STATUS_PENDING:
				return false;
			default:
				throw new Exception("Unknown status [$publishState]");
		}
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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		$this->validateJobDataObjectTypes($data);
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 */
	protected function validateJobDataObjectTypes(VidiunDistributionJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunVerizonVcastDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunVerizonVcastDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunVerizonVcastDistributionJobProviderData))
			throw new Exception("Provider data must be of type VidiunVerizonVcastDistributionJobProviderData");
	}
	
	/**
	 * @param string $path
	 * @param VidiunDistributionJobData $data
	 * @param VidiunVerizonDistributionProfile $distributionProfile
	 * @param VidiunVerizonDistributionJobProviderData $providerData
	 */
	public function handleSubmit(VidiunDistributionJobData $data, VidiunVerizonVcastDistributionProfile $distributionProfile, VidiunVerizonVcastDistributionJobProviderData $providerData)
	{
		$fileName = $data->entryDistribution->entryId . '_' . date('Y-m-d_H-i-s') . '.xml';
		VidiunLog::info('Sending file '. $fileName);
		
		$ftpManager = $this->getFTPManager($distributionProfile);
		$tmpFile = tmpfile();
		if ($tmpFile === false)
			throw new Exception('Failed to create tmp file');
		fwrite($tmpFile, $providerData->xml);
		rewind($tmpFile);
		$res = ftp_fput($ftpManager->getConnection(), $fileName, $tmpFile, FTP_ASCII);
		fclose($tmpFile);
		
		if ($res === false)
			throw new Exception('Failed to upload tmp file to ftp');
			
		$data->remoteId = $fileName;
		$data->sentData = $providerData->xml;
	}
	
	/**
	 * 
	 * @param VidiunVerizonVcastDistributionProfile $distributionProfile
	 * @return ftpMgr
	 */
	protected function getFTPManager(VidiunVerizonVcastDistributionProfile $distributionProfile)
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
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunVerizonVcastDistributionProfile))
			return VidiunLog::err("Distribution profile must be of type VidiunVerizonVcastDistributionProfile");
	
		$fileArray = $this->fetchFilesList($data->distributionProfile);
		
		for	($i=0; $i<count($fileArray); $i++)
		{
			if (preg_match ( "/{$data->remoteId}.rcvd/" , $fileArray[$i] , $matches))
			{
				return self::VERIZON_STATUS_PUBLISHED;
			}
			else if (preg_match ( "/{$data->remoteId}.*.err/" , $fileArray[$i] , $matches))
			{
				$res = preg_split ('/\./', $matches[0]);
				return $res[1];			
			}
		}

		return self::VERIZON_STATUS_PENDING;
	}

	/**
	 * @param VidiunVerizonDistributionProfile $distributionProfile
	 */
	protected function fetchFilesList(VidiunVerizonVcastDistributionProfile $distributionProfile)
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

}