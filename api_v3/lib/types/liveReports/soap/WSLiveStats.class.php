<?php


class WSLiveStats extends WSBaseObject
{				
	function getVidiunObject() {
		return new VidiunLiveStats();
	}
	
	/**
	 * @var long
	 **/
	public $audience;

	/**
	 * @var long
	 **/
	public $dvrAudience;

	/**
	 * @var float
	 **/
	public $avgBitrate;
	
	/**
	 * @var long
	 **/
	public $bufferTime;
	
	/**
	 * @var long
	 **/
	public $plays;
	
	/**
	 * @var long
	 **/
	public $secondsViewed;
	
	/**
	 * @var long
	 **/
	public $startEvent;
	
	/**
	 * @var long
	 **/
	public $timestamp;
	
}


