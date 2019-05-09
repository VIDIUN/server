<?php
/**
 * A string representation to return an array of strings
 * 
 * @see VidiunStringValueArray
 * @package api
 * @subpackage objects
 */
class VidiunStringValue extends VidiunValue
{
	/**
	 * @var string
	 */
    public $value;

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vStringValue();
			
		return parent::toObject($dbObject, $skip);
	}
}