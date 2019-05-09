<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunScope extends VidiunObject
{
	public function toObject($objectToFill = null, $propsToSkip = array())
	{
		if (is_null($objectToFill))
			$objectToFill = new vScope();

		return parent::toObject($objectToFill, $propsToSkip);
	}
}