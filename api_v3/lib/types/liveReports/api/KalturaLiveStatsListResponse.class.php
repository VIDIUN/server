<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveStatsListResponse extends VidiunListResponse
{				
	/**
	 *
	 * @var VidiunLiveStats
	 **/
	public $objects;
	
	public function getWSObject() {
		$obj = new WSLiveEntriesListResponse();
		$obj->fromVidiunObject($this);
		return $obj;
	}
	
}


