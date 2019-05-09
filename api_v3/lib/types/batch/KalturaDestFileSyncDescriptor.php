<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDestFileSyncDescriptor extends VidiunFileSyncDescriptor
{
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vDestFileSyncDescriptor();
			
		return parent::toObject($dbObject, $skip);
	}
}