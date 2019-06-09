<?php
/**
 * An int representation to return evaluated dynamic value
 * 
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunIntegerField extends VidiunIntegerValue
{
	/* (non-PHPdoc)
	 * @see VidiunIntegerValue::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!is_null($this->value) && !($this->value instanceof VidiunNullField))
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_NOT_UPDATABLE, $this->getFormattedPropertyNameWithClassName('value'));

		return parent::toObject($dbObject, $skip);
	}
}