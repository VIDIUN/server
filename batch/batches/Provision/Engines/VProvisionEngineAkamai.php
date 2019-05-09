<?php
/**
 * base class for the real ProvisionEngine in the system - currently only akamai 
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class VProvisionEngineAkamai extends VProvisionEngine
{
	/**
	 * @var AkamaiStreamsClient
	 */
	private $streamClient;

	/**
	 * @return string
	 */
	public function getName()
	{
		return get_class($this);
	}
	
	/**
	 * @param VidiunProvisionJobData $data
	 */
	protected function __construct(VidiunProvisionJobData $data = null)
	{
		$username = null;
		$password = null;
		
		if (!is_null($data) && $data instanceof VidiunAkamaiProvisionJobData)
		{
			//all fields are set and are not empty string
			if ($data->wsdlUsername && $data->wsdlPassword && $data->cpcode && $data->emailId && $data->primaryContact)
			{
				$username = $data->wsdlUsername;
				$password = $data->wsdlPassword;
			}
		}
		//if one of the params was not set, use the taskConfig data	
		if (!$username || !$password )
		{
			$username = VBatchBase::$taskConfig->params->wsdlUsername;
			$password = VBatchBase::$taskConfig->params->wsdlPassword;
		}
		
		VidiunLog::debug("Connecting to Akamai(username: $username, password: $password)");
		$this->streamClient = new AkamaiStreamsClient($username, $password);
	}
	
	/* (non-PHPdoc)
	 * @see batches/Provision/Engines/VProvisionEngine#provide()
	 */
	public function provide( VidiunBatchJob $job, VidiunProvisionJobData $data )
	{
		$cpcode = null;
		$emailId = null;
		$primaryContact = null;
		$secondaryContact = null;
		
		if ($data instanceof VidiunAkamaiProvisionJobData)
		{
			if ($data->wsdlUsername && $data->wsdlPassword)
			{
				$cpcode = $data->cpcode;
				$emailId = $data->emailId;
				$primaryContact = $data->primaryContact;
				$secondaryContact = $data->secondaryContact ? $data->secondaryContact : $data->primaryContact;
			}
		}
		//if one of the params was not set, use the taskConfig data		
		if (!$cpcode || !$emailId || !$primaryContact || !$secondaryContact)
		{
			$cpcode = VBatchBase::$taskConfig->params->cpcode;
			$emailId = VBatchBase::$taskConfig->params->emailId;
			$primaryContact = VBatchBase::$taskConfig->params->primaryContact;
			$secondaryContact = VBatchBase::$taskConfig->params->secondaryContact;
		}
		
		$name = $job->entryId;
		$encoderIP = $data->encoderIP;
		$backupEncoderIP = $data->backupEncoderIP;
		$encoderPassword = $data->encoderPassword;
		$endDate = $data->endDate;
		$dynamic = true;
		
		VidiunLog::debug("provideEntry(encoderIP: $encoderIP, backupEncoderIP: $backupEncoderIP, encoderPassword: $encoderPassword, endDate: $endDate)");
		$flashLiveStreamInfo = $this->streamClient->provisionFlashLiveDynamicStream($cpcode, $name, $encoderIP, $backupEncoderIP, $encoderPassword, $emailId, $primaryContact, $secondaryContact, $endDate, $dynamic);
		
		if(!$flashLiveStreamInfo)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: " . $this->streamClient->getError());
		}
		
		foreach($flashLiveStreamInfo as $field => $value)
			VidiunLog::info("Returned $field => $value");
				
		if(isset($flashLiveStreamInfo['faultcode']))
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: " . $flashLiveStreamInfo['faultstring']);
		}
		
		$arr = null;
		if(preg_match('/p\.ep(\d+)\.i\.akamaientrypoint\.net/', $flashLiveStreamInfo['primaryEntryPoint'], $arr))
			$data->streamID = $arr[1];
			
		if(preg_match('/b\.ep(\d+)\.i\.akamaientrypoint\.net/', $flashLiveStreamInfo['backupEntryPoint'], $arr))
			$data->backupStreamID = $arr[1];
			
		$data->rtmp = $flashLiveStreamInfo['connectUrl'];
		$data->encoderUsername = $flashLiveStreamInfo['encoderUsername'];
		$data->primaryBroadcastingUrl = 'rtmp://'.$flashLiveStreamInfo['primaryEntryPoint'].'/EntryPoint';
		$data->secondaryBroadcastingUrl = 'rtmp://'.$flashLiveStreamInfo['backupEntryPoint'].'/EntryPoint';
		$tempStreamName = explode('@', $flashLiveStreamInfo['streamName']);
		if (count($tempStreamName) == 2) {
			$data->streamName = $tempStreamName[0] . '_%i@' . $tempStreamName[1];
		}
		else {
			$data->streamName = $flashLiveStreamInfo['streamName'];
		}
		
		
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Succesfully provisioned entry', $data);
	}
	
	/* (non-PHPdoc)
	 * @see batches/Provision/Engines/VProvisionEngine#delete()
	 */
	public function delete( VidiunBatchJob $job, VidiunProvisionJobData $data )
	{
		$returnVal = $this->streamClient->deleteStream($data->streamID, true);
		
		if(!$returnVal)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: " . $this->streamClient->getError());
		}
		
		if(is_array($returnVal))
		{
			foreach($returnVal as $field => $value)
				VidiunLog::info("Returned $field => $value");
		}
		else
		{
			VidiunLog::info("Returned: $returnVal");
		}
				
		if(isset($returnVal['faultcode']))
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: " . $returnVal['faultstring']);
		}
		
		$data->returnVal = $returnVal;
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Succesfully deleted entry', $data);
	}
	
	
	/* (non-PHPdoc)
	 * @see VProvisionEngine::checkProvisionedStream()
	 */
	public function checkProvisionedStream(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		$data = $job->data;
		/* @var $data VidiunAkamaiUniversalProvisionJobData */
		$primaryEntryPoint = parse_url($data->primaryBroadcastingUrl, PHP_URL_HOST);
		$backupEntryPoint = parse_url($data->secondaryBroadcastingUrl, PHP_URL_HOST);
		if (!$primaryEntryPoint || !$backupEntryPoint)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Missing one or both entry points");
		}
		
		$pingTimeout = VBatchBase::$taskConfig->params->pingTimeout;
		@exec("ping -w $pingTimeout $primaryEntryPoint", $output, $return);
		if ($return)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::ALMOST_DONE, "No reponse from primary entry point - retry in 5 mins");
		}
		
		@exec("ping -w $pingTimeout $backupEntryPoint", $output, $return);
		if ($return)
		{
			return new VProvisionEngineResult(VidiunBatchJobStatus::ALMOST_DONE, "No reponse from backup entry point - retry in 5 mins");
		}
		
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, "Stream is Provisioned");
		
	}

}

