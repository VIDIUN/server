<?php
/**
 * Represents the current request user agent context
 * 
 * @package api
 * @subpackage objects
 */
class VidiunUserAgentContextField extends VidiunStringField
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vUserAgentContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}