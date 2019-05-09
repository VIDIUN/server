<?php
/**
 * @package plugins.velocix
 * @subpackage storage
 */
class DeliveryProfileVelocixLiveHds extends DeliveryProfileLiveHds
{
	
	public function setHdsManifestContentType($v)
	{
		$this->putInCustomData("hdsManifestContentType", $v);
	}
	
	public function getHdsManifestContentType()
	{
		return $this->getFromCustomData("hdsManifestContentType", null, 'text/plain');
	}
	
	/**
	 * @return vUrlTokenizer
	 */
	public function getTokenizer()
	{
		// For configuration purposes. 
		if(is_null($this->params->getEntryId())) 
			return parent::getTokenizer();
				
		$liveEntry = entryPeer::retrieveByPK($this->params->getEntryId());
		//if stream name doesn't start with 'auth' than the url stream is not tokenized
		if ($liveEntry && substr($liveEntry->getStreamName(), 0, 4) == 'auth') {
			$token = parent::getTokenizer();
			$token->setStreamName($liveEntry->getStreamName());
			$token->setProtocol('hds');
			return $token;
		}
		
		return null;
	}
	
	protected function getParamName() {
		$tokenizer = $this->getTokenizer();
		if($tokenizer && ($tokenizer instanceof vVelocixUrlTokenizer)) 
			return $tokenizer->getParamName();
		return '';
	}
	
	public function checkIsLive($url){
		
		VidiunLog::info('url to check:'.$url);
		$parts = parse_url($url);
		parse_str($parts['query'], $query);
		$token = $query[$this->getParamName()];
		$data = $this->urlExists($url, array($this->getHdsManifestContentType()));
		if(!$data)
		{
			VidiunLog::Info("URL [$url] returned no valid data. Exiting.");
			return false;
		}
		VidiunLog::info('Velocix HDS manifest data:'.$data);
		$dom = new VDOMDocument();
		$dom->loadXML($data);
		$element = $dom->getElementsByTagName('baseURL')->item(0);
		if(!$element){
			VidiunLog::Info("No base url was given");
			return false;
		}
		$baseUrl = $element->nodeValue;
		foreach ($dom->getElementsByTagName('media') as $media){
			$href = $media->getAttribute('href');
			$streamUrl = $baseUrl.$href;
			$streamUrl .= $token ? '?'.$this->getParamName()."=$token" : '' ;
			if($this->urlExists($streamUrl, array(),'0-0')  !== false){
				VidiunLog::info('is live:'.$streamUrl);
				return true;
			}
		}
		return false;
	}
	
}
