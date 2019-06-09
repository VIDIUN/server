<?php


class WSLiveReportInputFilter extends WSBaseObject
{	
	function getVidiunObject() {
		return new VidiunLiveReportInputFilter();
	}
				
	/**
	 * @var string
	 **/
	public $entryIds;
	
	/**
	 * @var long
	 **/
	public $fromTime;
	
	/**
	 * @var long
	 **/
	public $toTime;
	
	/**
	 * @var boolean
	 **/
	public $live;
	
	/**
	 * @var long
	 **/
	public $partnerId;
	
	/**
	 * @var string
	 */
	public $orderBy;
	
}


