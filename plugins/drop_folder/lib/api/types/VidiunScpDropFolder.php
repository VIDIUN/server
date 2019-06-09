<?php

/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunScpDropFolder extends VidiunSshDropFolder
{
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new ScpDropFolder();
			
		parent::toObject($dbObject, $skip);
					
		return $dbObject;
	}
}