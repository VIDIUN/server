<?php


class WSEntryReferrerLiveStats extends WSEntryLiveStats
{			
	function getVidiunObject() {
		return new VidiunEntryReferrerLiveStats();
	}
	
	/**
	 * @var string
	 **/
	public $referrer;
	
}


