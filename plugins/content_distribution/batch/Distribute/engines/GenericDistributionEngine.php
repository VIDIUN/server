<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class GenericDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineReport,
	IDistributionEngineDelete,
	IDistributionEngineCloseUpdate,
	IDistributionEngineCloseSubmit,
	IDistributionEngineCloseReport,
	IDistributionEngineCloseDelete
{
	protected $tempXmlPath;
	
	/* (non-PHPdoc)
	 * @see DistributionEngine::configure()
	 */
	public function configure()
	{
		parent::configure();
		
		if(VBatchBase::$taskConfig->params->tempXmlPath)
		{
			$this->tempXmlPath = VBatchBase::$taskConfig->params->tempXmlPath;
			if(!is_dir($this->tempXmlPath))
				vFile::fullMkfileDir($this->tempXmlPath, 0777, true);
		}
		else
		{
			VidiunLog::err("params.tempXmlPath configuration not supplied");
			$this->tempXmlPath = sys_get_temp_dir();
		}
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunGenericDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunGenericDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunGenericDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunGenericDistributionJobProviderData");
		
		return $this->handleAction($data, $data->distributionProfile, $data->distributionProfile->submitAction, $data->providerData);
	}

	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunGenericDistributionProfile $distributionProfile
	 * @param VidiunGenericDistributionJobProviderData $providerData
	 * @throws Exception
	 * @throws vFileTransferMgrException
	 * @return boolean true if finished, false if will be finished asynchronously
	 */
	protected function handleAction(VidiunDistributionJobData $data, VidiunGenericDistributionProfile $distributionProfile, VidiunGenericDistributionProfileAction $distributionProfileAction, VidiunGenericDistributionJobProviderData $providerData)
	{
		if(!$providerData->xml)
			throw new Exception("XML data not supplied");
		
		$fileName = uniqid() . '.xml';
		$srcFile = $this->tempXmlPath . '/' . $fileName;
		$destFile = $distributionProfileAction->serverPath;
			
		if($distributionProfileAction->protocol != VidiunDistributionProtocol::HTTP && $distributionProfileAction->protocol != VidiunDistributionProtocol::HTTPS)
			$destFile .= '/' . $fileName;
			
		$destFile = str_replace('{REMOTE_ID}', $data->remoteId, $destFile);
		
		file_put_contents($srcFile, $providerData->xml);
		VidiunLog::log("XML written to file [$srcFile]");
		
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$engineOptions['passiveMode'] = $distributionProfileAction->ftpPassiveMode;
		$engineOptions['fieldName'] = $distributionProfileAction->httpFieldName;
		$engineOptions['fileName'] = $distributionProfileAction->httpFileName;
		$fileTransferMgr = vFileTransferMgr::getInstance($distributionProfileAction->protocol, $engineOptions);
		if(!$fileTransferMgr)
			throw new Exception("File transfer manager type [$distributionProfileAction->protocol] not supported");
			
		$fileTransferMgr->login($distributionProfileAction->serverUrl, $distributionProfileAction->username, $distributionProfileAction->password);
		$fileTransferMgr->putFile($destFile, $srcFile, true);
		$results = $fileTransferMgr->getResults();
		
		if($results && is_string($results))
		{
			$data->results = $results;
			$parsedValues = $this->parseResults($results, $providerData->resultParserType, $providerData->resultParseData);
			if(count($parsedValues))
				list($data->remoteId) = $parsedValues;
		}
		$data->sentData = $providerData->xml;
		
		return true;
	}

	/**
	 * @param string $results
	 * @param VidiunGenericDistributionProviderParser $resultParserType
	 * @param string $resultParseData
	 * @return array of parsed values
	 */
	protected function parseResults($results, $resultParserType, $resultParseData)
	{
		switch($resultParserType)
		{
			case VidiunGenericDistributionProviderParser::XSL;
				$xml = new DOMDocument();
				if(!$xml->loadXML($results))
					return false;
		
				$xsl = new DOMDocument();
				$xsl->loadXML($resultParseData);
				
				$proc = new XSLTProcessor;
				$proc->registerPHPFunctions(vXml::getXslEnabledPhpFunctions());
				$proc->importStyleSheet($xsl);
				
				$data = $proc->transformToDoc($xml);
				if(!$data)
					return false;
					
				return explode(',', $data);
				
			case VidiunGenericDistributionProviderParser::XPATH;
				$xml = new DOMDocument();
				if(!$xml->loadXML($results))
					return false;
		
				$xpath = new DOMXPath($xml);
				$elements = $xpath->query($resultParseData);
				if(is_null($elements))
					return false;
					
				$matches = array();
				foreach ($elements as $element)
					$matches[] = $element->textContent;
					
				return $matches;;
				
			case VidiunGenericDistributionProviderParser::REGEX;
				$matches = array();
				if(!preg_match("/$resultParseData/", $results, $matches))
					return false;
					
				return array_shift($matches);
				
			default;
				return false;
		}
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		// not supported
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunGenericDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunGenericDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunGenericDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunGenericDistributionJobProviderData");
		
		return $this->handleAction($data, $data->distributionProfile, $data->distributionProfile->deleteAction, $data->providerData);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		// not supported
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseReport::closeReport()
	 */
	public function closeReport(VidiunDistributionFetchReportJobData $data)
	{
		// not supported
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		// not supported
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineReport::fetchReport()
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunGenericDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunGenericDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunGenericDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunGenericDistributionJobProviderData");
		
		return $this->handleFetchReport($data, $data->distributionProfile, $data->distributionProfile->report, $data->providerData);
	}


	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunGenericDistributionProfile $distributionProfile
	 * @param VidiunGenericDistributionJobProviderData $providerData
	 * @throws Exception
	 * @throws vFileTransferMgrException
	 * @return boolean true if finished, false if will be finished asynchronously
	 */
	protected function handleFetchReport(VidiunDistributionFetchReportJobData $data, VidiunGenericDistributionProfile $distributionProfile, VidiunGenericDistributionProfileAction $distributionProfileAction, VidiunGenericDistributionJobProviderData $providerData)
	{
		$srcFile = str_replace('{REMOTE_ID}', $data->remoteId, $distributionProfileAction->serverPath);
		
		VidiunLog::log("Fetch report from url [$srcFile]");
		$results = file_get_contents($srcFile);
	
		if($results && is_string($results))
		{
			$data->results = $results;
			$parsedValues = $this->parseResults($results, $providerData->resultParserType, $providerData->resultParseData);
			if(count($parsedValues))
				list($data->plays, $data->views) = $parsedValues;
		}
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunGenericDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunGenericDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunGenericDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunGenericDistributionJobProviderData");
		
		return $this->handleAction($data, $data->distributionProfile, $data->distributionProfile->updateAction, $data->providerData);
	}

}