<?php
/**
 * Evaluates object ID according to given context
 * 
 * @package api
 * @subpackage objects
 */
class VidiunObjectIdField extends VidiunStringField
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vObjectIdField();
			
		return parent::toObject($dbObject, $skip);
	}
}