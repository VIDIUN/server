<?php
/**
 * An int representation to return an array of ints
 * 
 * @see VidiunIntegerValueArray
 * @package api
 * @subpackage objects
 */
class VidiunIntegerValue extends VidiunValue
{
	/**
	 * @var int
	 */
    public $value;

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vIntegerValue();
			
		return parent::toObject($dbObject, $skip);
	}
}