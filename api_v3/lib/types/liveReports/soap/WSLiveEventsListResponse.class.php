<?php


class WSLiveEventsListResponse extends WSBaseObject
{				
	function getVidiunObject() {
		return new VidiunLiveEventsListResponse();
	}
	
	/**
	 * @var array
	 **/
	public $objects;
	
	/**
	 * @var int
	 **/
	public $totalCount;
	
}


