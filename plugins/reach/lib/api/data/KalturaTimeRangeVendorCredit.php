<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */

class VidiunTimeRangeVendorCredit extends VidiunVendorCredit
{
	/**
	 *  @var time
	 */
	public $toDate;

	private static $map_between_objects = array (
		'toDate',
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
			$dbObject = new vTimeRangeVendorCredit();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("toDate");
		
		parent::validateForInsert($propertiesToSkip);

		if ($this->fromDate > $this->toDate)
			throw new VidiunAPIException(VidiunReachErrors::INVALID_CREDIT_DATES , $this->fromDate, $this->toDate);
	}
	
	public function hasObjectChanged($sourceObject)
	{
		if(parent::hasObjectChanged($sourceObject))
			return true;
		
		/* @var $sourceObject vTimeRangeVendorCredit */
		if($this->toDate && $this->toDate != $sourceObject->getToDate())
			return true;
		
		return false;
	}
}
