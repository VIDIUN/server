<?php
/**
 * Provision Engine to provision new Velocix live stream	
 * 
 * @package plugins.velocix
 * @subpackage lib
 */
class VProvisionEngineVelocix extends VProvisionEngine
{
	private $baseServiceUrl;
	
	private $password;
	
	private $userName;
	
	private $streamName;
	
	const APPLE_HTTP_URLS = 'applehttp_urls';
	const HDS_URLS = 'hds_urls';
	const SL_URLS = 'sl_urls';
	const PLAYBACK = 'playback';
	const PUBLISH = 'publish';
	
	public function __construct()
	{
		if (! VBatchBase::$taskConfig->params->restapi->velocixApiBaseServiceUrl)
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: velocixApiBaseServiceUrl is missing from worker configuration. Cannot provision stream");
		
		$this->baseServiceUrl = VBatchBase::$taskConfig->params->restapi->velocixApiBaseServiceUrl;
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
		if (! VBatchBase::$taskConfig->params->restapi->velocixPlaybackHost)
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: velocixPlaybackHost is missing from worker configuration. Cannot provision stream"); 
		
		if (! VBatchBase::$taskConfig->params->restapi->velocixPublishHost)
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, "Error: velocixPublish is missing from worker configuration. Cannot provision stream");  
		
		$this->password = $data->password;
		$this->userName = $data->userName;
		$this->streamName = $data->streamName;
		
		if (!$this->createVelocixAsset())
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, 'Failed to create Velocix asset', $data);
			
		if (!$this->createAssetProfile($data->provisioningParams))
			return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, 'Failed to create Velocix asset profile', $data);
		
		$data->provisioningParams = $this->updateDataWithUrls($data->provisioningParams);
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Succesfully provisioned entry', $data);
		
	}
	
	private function updateDataWithUrls( $provisioningParams){
		$playbackHost = VBatchBase::$taskConfig->params->restapi->velocixPlaybackHost;
		$publishHost = VBatchBase::$taskConfig->params->restapi->velocixPublishHost;
		$hdsPlaybackPrefix = VBatchBase::$taskConfig->params->restapi->velocixHDSPlaybackPrefix;
		foreach ($provisioningParams as $provisioningParam){
			switch ($provisioningParam->key){
				case VidiunPlaybackProtocol::HDS:
					$keyValUrls = new VidiunKeyValue();
					$keyValUrls->key = self::HDS_URLS;
					$urls = array(self::PLAYBACK => 'http://'.$hdsPlaybackPrefix.'/'.$this->streamName.'/hds/'.$this->streamName.'.f4m',
								self::PUBLISH =>'rtmp://'.$publishHost.'/livepkgr/'.$this->streamName.'/%i?adbe-live-event=liveevent');
					$keyValUrls->value= serialize($urls);
					$provisioningParams[] = $keyValUrls;
					break;
				case VidiunPlaybackProtocol::APPLE_HTTP:
					$keyValUrls = new VidiunKeyValue();
					$keyValUrls->key = self::APPLE_HTTP_URLS;
					$urls = array(self::PLAYBACK => 'http://'.$playbackHost.'/'.$this->streamName.'/hls/'.$this->streamName.'.m3u8',
								self::PUBLISH =>'http://'.$publishHost.'/'.$this->streamName.'/hls/'.$this->streamName);
					$keyValUrls->value= serialize($urls);
					$provisioningParams[] = $keyValUrls;
					break;
				case VidiunPlaybackProtocol::SILVER_LIGHT:
					$keyValUrls = new VidiunKeyValue();
					$keyValUrls->key = self::SL_URLS;
					$urls = array(self::PLAYBACK => 'http://'.$playbackHost.'/'.$this->streamName.'/smooth/'.$this->streamName.'.isml/Manifest',
								 self::PUBLISH =>'http://'.$publishHost.'/'.$this->streamName.'/smooth/'.$this->streamName.'.isml');
					$keyValUrls->value= serialize($urls);
					$provisioningParams[] = $keyValUrls;
					break;
			}
		}
		return $provisioningParams;
	}
	
	private function createVelocixAsset(){
		$url = $this->baseServiceUrl . "/vxoa/assets/";
		$data = array(
				'asset_name' => $this->streamName,
				'dest_path' => $this->streamName,
				'asset_type' => 'live'
				);
		$data = json_encode($data);
		$res = $this->doCurl($url, $data);
		VidiunLog::info('Velocix asset creation response:'.$res);
		return strstr($res, '201 Created') ? true :  false;
	}
	
	private function createAssetProfile($provisioningParams){
		$url = $this->baseServiceUrl . "/vxoa/assets/".$this->streamName.'/formats';
		$data = array();
		foreach ($provisioningParams as $provisioningParam){
			/* @var $provisioningParam VidiunKeyValue */
			if ($provisioningParam->key == VidiunPlaybackProtocol::SILVER_LIGHT)
				$playbackProfile = 'smooth';
			elseif ($provisioningParam->key == VidiunPlaybackProtocol::APPLE_HTTP)
				$playbackProfile = 'hls';
			else
				$playbackProfile = $provisioningParam->key;
			$configuratioArray = array();
			$configuratioArray['profile'] = $playbackProfile;
			$configuratioArray['sources'] = array();
			$urlNum = 1;
			$bitrates = explode(',',$provisioningParam->value);
			$isFirst = true;
			foreach ($bitrates as $bitrate){
				if ($provisioningParam->key == VidiunPlaybackProtocol::SILVER_LIGHT)
					$publishProfile = 'piff';
				elseif ($provisioningParam->key == VidiunPlaybackProtocol::APPLE_HTTP)
					$publishProfile = 'hls';
				else
					$publishProfile = $provisioningParam->key;
				$source = array();
				$source['bitrate'] = $bitrate;
				$source['delete'] = 'null';
				$source['profile'] = $publishProfile;
				//for silver light the first resource url should be the stream name 
				$source['url'] = ($isFirst && $provisioningParam->key == VidiunPlaybackProtocol::SILVER_LIGHT) ? $this->streamName : strval($urlNum++);
				$configuratioArray['sources'][] = $source;
				$isFirst = false;
			}
			$data = json_encode($configuratioArray);
			$data = trim($data,'[]');
			$res = $this->doCurl($url, $data);
			VidiunLog::info('Velocix profile creation response:'.$res);
			if (strstr($res, '201 Created') == false) 
				return false;
		}
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see VProvisionEngine::delete()
	 */
	public function delete(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		$this->password = $data->password;
		$this->userName = $data->userName;
		$url = $this->baseServiceUrl . "/vxoa/assets/".$data->streamName;
		$res = $this->doCurl($url, null, true);
		VidiunLog::info('Velocix asset delete response:'.$res);
		if ( strstr($res, '200 OK') )
			return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, 'Succesfully deleted entry', $data);	
		return new VProvisionEngineResult(VidiunBatchJobStatus::FAILED, 'Failed to delete Velocix asset', $data);
	}
	

	/* (non-PHPdoc)
	 * @see VProvisionEngine::checkProvisionedStream()
	 */
	public function checkProvisionedStream(VidiunBatchJob $job, VidiunProvisionJobData $data) 
	{
		return new VProvisionEngineResult(VidiunBatchJobStatus::FINISHED, "Stream is in status Provisioned");
	}
	
	private function doCurl($url, $data = null, $isDelete = false){
		VidiunLog::info("curl url:[$url] user:[$this->userName] password:[$this->password]");
		VidiunLog::info("Sent data:".$data);
		$ch = curl_init($url);
		if ($isDelete)
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		else
			curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_USERPWD, "$this->userName:$this->password");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json','Accept: application/json'));
		if ($data)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		return curl_exec($ch);
	}

	
}