<?php
/**
 * Represents the current request IP address context 
 * 
 * @package api
 * @subpackage objects
 */
class VidiunIpAddressContextField extends VidiunStringField
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vIpAddressContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}