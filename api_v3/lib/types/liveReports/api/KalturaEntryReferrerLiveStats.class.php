<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunEntryReferrerLiveStats extends VidiunEntryLiveStats
{			
	/**
	 * @var string
	 **/
	public $referrer;
	
	public function getWSObject() {
		$obj = new WSEntryReferrerLiveStats();
		$obj->fromVidiunObject($this);
		return $obj;
	}
}


