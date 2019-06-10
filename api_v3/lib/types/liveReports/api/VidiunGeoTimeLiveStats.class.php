<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunGeoTimeLiveStats extends VidiunEntryLiveStats
{	
	/**
	 * @var VidiunCoordinate
	 **/
	public $city;
	
	/**
	 * @var VidiunCoordinate
	 **/
	public $country;
	
	public function getWSObject() {
		$obj = new WSGeoTimeLiveStats();
		$obj->fromVidiunObject($this);
		return $obj;
	}
}


