<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunEntryLiveStats extends VidiunLiveStats
{				
	/**
	 * @var string
	 **/
	public $entryId;
	
	/**
	 * @var int
	 */
	public $peakAudience;

	/**
	 * @var int
	 */
	public $peakDvrAudience;
	
	public function getWSObject() {
		$obj = new WSEntryLiveStats();
		$obj->fromVidiunObject($this);
		return $obj;
	}
	
}


