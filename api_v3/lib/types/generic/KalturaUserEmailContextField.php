<?php
/**
 * Represents the current session user e-mail address context
 * 
 * @package api
 * @subpackage objects
 */
class VidiunUserEmailContextField extends VidiunStringField
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vUserEmailContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}