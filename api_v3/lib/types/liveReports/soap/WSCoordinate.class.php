<?php

class WSCoordinate extends WSBaseObject
{	
	function getVidiunObject() {
		return new VidiunCoordinate();
	}
				
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
	
}


