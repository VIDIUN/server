<?php


class WSAnalyticsException extends WSBaseObject
{	
	/** 
	 * Currently, this object doesn't have a Vidiun API representation
	 * and it is used only to enable parsing the exception if recieved from the client
	 */
	function getVidiunObject() {
		return null;
	}
				
	/**
	 * @var string
	 **/
	public $message;
	
}


