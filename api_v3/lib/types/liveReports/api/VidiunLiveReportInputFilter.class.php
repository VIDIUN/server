<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveReportInputFilter extends VidiunObject
{	
	/**
	 * @var string
	 **/
	public $entryIds;
	
	/**
	 * @var time
	 **/
	public $fromTime;
	
	/**
	 * @var time
	 **/
	public $toTime;
	
	/**
	 * @var VidiunNullableBoolean
	 **/
	public $live;
	
	/**
	 * @var VidiunLiveReportOrderBy
	 */
	public $orderBy;
	
	public function getWSObject() {
		$obj = new WSLiveReportInputFilter();
		$obj->fromVidiunObject($this);
		return $obj;
	}
}


