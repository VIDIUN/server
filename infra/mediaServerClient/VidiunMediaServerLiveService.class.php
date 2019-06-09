<?php

require_once(__DIR__ . '/VidiunMediaServerClient.class.php');
	
class VidiunMediaServerLiveService extends VidiunMediaServerClient
{
	function __construct($url)
	{
		parent::__construct($url);
	}
	
	
	/**
	 * 
	 * @param string $liveEntryId
	 * @return VidiunMediaServerSplitRecordingNowResponse
	 **/
	public function splitRecordingNow($liveEntryId)
	{
		$params = array();
		
		$params["liveEntryId"] = $this->parseParam($liveEntryId, 'xsd:string');

		return $this->doCall("splitRecordingNow", $params, 'VidiunMediaServerSplitRecordingNowResponse');
	}
	
}		
	
