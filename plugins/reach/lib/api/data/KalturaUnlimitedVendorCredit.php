<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */

class VidiunUnlimitedVendorCredit extends VidiunBaseVendorCredit
{
	/**
	 *  @var int
	 *  @readonly
	 */
	public $credit = ReachProfileCreditValues::UNLIMITED_CREDIT;
	
	/**
	 *  @var time
	 */
	public $fromDate;
	
	/**
	 *  @var time
	 */
	public $toDate;

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	private static $map_between_objects = array (
		'fromDate',
		'credit',
		'toDate',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	*/
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("fromDate");
		parent::validateForInsert(array("credit"));

	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vUnlimitedVendorCredit();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}
	
	public function hasObjectChanged($sourceObject)
	{
		if(parent::hasObjectChanged($sourceObject))
			return true;
		
		/* @var $sourceObject vUnlimitedVendorCredit */
		if( ($this->credit && $this->credit != $sourceObject->getCredit())
			|| ($this->fromDate && $this->fromDate != $sourceObject->getFromDate())
		)
			return true;
		
		return false;
	}
}
