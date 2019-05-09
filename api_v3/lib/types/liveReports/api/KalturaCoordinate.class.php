<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunCoordinate extends VidiunObject
{	
	/**
	 * @var float
	 **/
	public $latitude;
	
	/**
	 * @var float
	 **/
	public $longitude;
	
	/**
	 * @var string
	 **/
	public $name;
	
	public function getWSObject() {
		$obj = new WSCoordinate();
		$obj->fromVidiunObject($this);
		return $obj;
	}
}


