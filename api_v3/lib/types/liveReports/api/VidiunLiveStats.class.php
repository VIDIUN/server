<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveStats extends VidiunObject
{				
	/**
	 *
	 * @var int
	 **/
	public $audience = 0;

	/**
	 *
	 * @var int
	 **/
	public $dvrAudience = 0;

	/**
	 *
	 * @var float
	 **/
	public $avgBitrate = 0;
	
	/**
	 *
	 * @var int
	 **/
	public $bufferTime = 0;
	
	/**
	 *
	 * @var int
	 **/
	public $plays = 0;
	
	/**
	 *
	 * @var int
	 **/
	public $secondsViewed = 0;
	
	/**
	 *
	 * @var bigint
	 **/
	public $startEvent;
	
	/**
	 *
	 * @var time
	 **/
	public $timestamp;
	
	public function getWSObject() {
		$obj = new WSLiveStats();
		$obj->fromVidiunObject($this);
		return $obj;
	}
	
}


