<?php
/**
 * Provision Engine to provision new Akamai HLS+HDS live stream	
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class VProvisionEngineUniversalAkamai extends VProvisionEngine
{
	public $systemUser;
	
	public $systemPassword;
	
	public $domainName;
	
	public static $baseServiceUrl;
	
	const PROVISIONED = 'Provisioned';
	
	const PENDING = 'Pending';

	const NOT_YET_PROVISIONED = 'Not yet provisioned';
	
	/**
	 * @var AkamaiUniversalStreamClient
	 */
	protected $streamClient;
	
	protected function __construct(VidiunAkamaiUniversalProvisionJobData $data)
	{
		if (!VBatchBase::$taskConfig->params->restapi->akamaiRestApiBaseServiceUrl)
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: akamaiRestApiBaseServiceUrl is missing from worker configuration. Cannot provision stream"); 
		
		self::$baseServiceUrl = VBatchBase::$taskConfig->params->restapi->akamaiRestApiBaseServiceUrl;
		
		if (!is_null($data) && $data instanceof VidiunAkamaiUniversalProvisionJobData)
		{
			//all fields are set and are not empty string
			if ($data->systemUserName && $data->systemPassword && $data->domainName)
			{ 
				$this->systemUser = $data->systemUserName;
				$this->systemPassword = $data->systemPassword;
				$this->domainName = $data->domainName;
			}
		}
		//if one of the params was not set, use the taskConfig data	
		if (!$this->systemUser || !$this->systemPassword || !$this->domainName)
		{
			$this->systemUser = VBatchBase::$taskConfig->params->restapi->systemUserName;
			$this->systemPassword = VBatchBase::$taskConfig->params->restapi->systemPassword;
			$this->domainName = VBatchBase::$taskConfig->params->restapi->domainName;
			$data->primaryContact = VBatchBase::$taskConfig->params->restapi->primaryContact;
			$data->secondaryContact = VBatchBase::$taskConfig->params->restapi->secondaryContact;
			$data->notificationEmail = VBatchBase::$taskConfig->params->restapi->notificationEmail;
		}
	}
	
	/* (non-PHPdoc)
	 * @see VProvisionEngine::getName()
	 */
	public function getName() {
		return get_class($this);
		
	}

	/* (non-PHPdoc)
	 * @see VProvisionEngine::provide()
	 */
	public function provide(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		$res = $this->provisionStream($data);
		if (!$res)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: no result received for connection");
		}
		
		VidiunLog::info ("Request to provision stream returned result: $res");
		$resultXML = new SimpleXMLElement($res);
		//In this case, REST API has returned an API error.
		$errors = $resultXML->xpath('error');
		if ($errors && count($errors))
		{
			//There is always only 1 error listed in the XML
			$error = $errors[0];
			return new VProvisionEngineResult(VidiunBatchJobStatus::RETRY, "Error: ". strval($error[0]));
		}
		//Otherwise, the stream provision request probably returned OK, attempt to parse it as a new stream XML
		try {
			$data = $this->fromStreamXML($resultXML, $data);
		}
		catch (Exception $e)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: ". $e->getMessage());
		}

		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Successfully provisioned entry', $data);
		
	}
	
	/**
	 * Function to provision the stream using the Akamai RestAPI
	 * @param VidiunAkamaiUniversalProvisionJobData $data
	 * @return mixed
	 */
	private function provisionStream (VidiunAkamaiUniversalProvisionJobData $data)
	{
		$url = self::$baseServiceUrl . "/{$this->domainName}/stream";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getStreamXML($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->systemUser}:{$this->systemPassword}");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
		return curl_exec($ch);
	}
	
	/**
	 * Construct stream using the job data
	 * @param VidiunAkamaiUniversalProvisionJobData $data
	 * @return string
	 */
	private function getStreamXML (VidiunAkamaiUniversalProvisionJobData $data)
	{
		$result = new SimpleXMLElement("<stream/>");
		$result->addChild("stream-type", $data->streamType);
		$result->addChild("stream-name", $data->streamName);
		$result->addChild("primary-contact-name", $data->primaryContact);
		$result->addChild("secondary-contact-name", $data->secondaryContact);
		$result->addChild("notification-email", $data->notificationEmail);
		
		$encoderSettings = $result->addChild("encoder-settings");
		$encoderSettings->addChild("primary-encoder-ip", $data->encoderIP);
		$encoderSettings->addChild("backup-encoder-ip", $data->backupEncoderIP);
		$encoderSettings->addChild("password", $data->encoderPassword);
		
		$dvrSettings = $result->addChild("dvr-settings");
		$dvrSettings->addChild("dvr", $data->dvrEnabled ? "Enabled" : "Disabled");
		$dvrSettings->addChild("dvr-window", $data->dvrWindow);
		
		return $result->saveXML();
	}
	
	private function fromStreamXML (SimpleXMLElement $xml, VidiunAkamaiUniversalProvisionJobData $data)
	{
		$data->streamID = $this->getXMLNodeValue('stream-id', $xml);
		if (!$data->streamID)
		{
			throw new Exception("Necessary parameter stream-id missing from returned result");
		}
		
		$data->streamName = $this->getXMLNodeValue('stream-name', $xml);
		$encoderSettingsNodeName = 'encoder-settings';
		$encoderSettings = $xml->$encoderSettingsNodeName;
		$data->encoderUsername = strval($encoderSettings->username);
		if (!$data->encoderUsername)
		{
			throw new Exception("Necessary parameter [username] missing from returned result");
		}		
		//Parse encoding primary and secondary entry points
		$entryPoints = $xml->xpath('/stream/entrypoints/entrypoint');
		if (!$entryPoints || !count($entryPoints))
			throw new Exception('Necessary configurations for entry points missing from the returned result');
			
		foreach ($entryPoints as $entryPoint)
		{
			/* @var $entryPoint SimpleXMLElement */
			$domainNodeName = 'domain-name';
			$domainName = $entryPoint->$domainNodeName;
			if (!$domainName)
			{
				throw new Exception('Necessary URL for entry point missing from the returned result');
			}
			if (strval($entryPoint->type) == 'Backup')
			{
				$data->secondaryBroadcastingUrl = "rtmp://".$domainName . "/EntryPoint";
			}
			else
			{
				$data->primaryBroadcastingUrl = "rtmp://". $domainName . "/EntryPoint";
			}
		}
		
		return $data;
	}
	
	/**
	 * @param string $nodeName
	 * @param SimpleXMLElement $xml
	 * @return string
	 */
	private function getXMLNodeValue ($nodeName, SimpleXMLElement $xml)
	{
		return strval($xml->$nodeName);
	}
	
	/* (non-PHPdoc)
	 * @see VProvisionEngine::delete()
	 */
	public function delete(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		VidiunLog::info("Deleting stream with ID [". $data->streamID ."]" );
		
		$url = self::$baseServiceUrl . "/{$this->domainName}/stream/".$data->streamID;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->systemUser}:{$this->systemPassword}");
		$result = curl_exec($ch);
		
		if (!$result)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: failed to call RestAPI");
		}
		
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (VCurlHeaderResponse::isError($httpCode))
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: delete failed");
		
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Succesfully deleted stream', $data);
	}
	

	/* (non-PHPdoc)
	 * @see VProvisionEngine::checkProvisionedStream()
	 */
	public function checkProvisionedStream(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		$url = self::$baseServiceUrl . "/{$this->domainName}/stream/".$data->streamID;
		VidiunLog::info("Retrieving stream with ID [". $data->streamID ."] from URL [$url]" );
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->systemUser}:{$this->systemPassword}");
		$result = curl_exec($ch);
		
		if (!$result)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: failed to call RestAPI");
		}
		
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		 if (VCurlHeaderResponse::isError($httpCode))
			return new VProvisionEngineResult(VidiunBatchJobStatus::RETRY, "Error: retrieval failed , retrying. HTTP Error code:".$httpCode);
		
		VidiunLog::info("Result received: $result");
		$resultXML = new SimpleXMLElement($result);
		$errors = $resultXML->xpath('error');
		if ($errors && count($errors))
		{
			//There is always only 1 error listed in the XML
			$error = $errors[0];
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: ". strval($error[0]));
		}
		
		if ($resultXML->status)
		{
			switch (strval($resultXML->status))
			{
				case self::PENDING:
				case self::NOT_YET_PROVISIONED:
					return new VProvisionEngineResult(VidiunBatchJobStatus::ALMOST_DONE, "Stream is still in status Pending - retry in 5 minutes");
					break;
				case self::PROVISIONED:
					return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, "Stream is in status Provisioned");
					break;
			}
		}
		
		return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Unable to retrieve valid status from result of Akamai REST API");
	}

	
}
