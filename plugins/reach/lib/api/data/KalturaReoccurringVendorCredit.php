<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */

class VidiunReoccurringVendorCredit extends VidiunTimeRangeVendorCredit
{
	/**
	 * @var VidiunVendorCreditRecurrenceFrequency
	 */
	public $frequency;

	private static $map_between_objects = array (
		'frequency',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vReoccurringVendorCredit();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */	 
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("frequency");
		
		parent::validateForInsert($propertiesToSkip);
	}
	
	public function hasObjectChanged($sourceObject)
	{
		if(parent::hasObjectChanged($sourceObject))
			return true;
		
		/* @var $sourceObject vReoccurringVendorCredit */
		if($this->frequency && $this->frequency != $sourceObject->getFrequency())
			return true;
		
		return false;
	}
}
