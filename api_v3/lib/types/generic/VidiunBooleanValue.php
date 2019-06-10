<?php
/**
 * A boolean representation to return an array of booleans
 * 
 * @see VidiunBooleanValueArray
 * @package api
 * @subpackage objects
 */
class VidiunBooleanValue extends VidiunValue
{
	/**
	 * @var bool
	 */
    public $value;

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vBooleanValue();
			
		return parent::toObject($dbObject, $skip);
	}
}