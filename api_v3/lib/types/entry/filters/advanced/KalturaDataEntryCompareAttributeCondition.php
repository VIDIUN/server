<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunDataEntry attributes. Use VidiunDataEntryCompareAttribute enum to provide attribute name.
*/
class VidiunDataEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunDataEntryCompareAttribute
	 */
	public $attribute;

	private static $mapBetweenObjects = array
	(
		"attribute" => "attribute",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects() , self::$mapBetweenObjects);
	}

	protected function getIndexClass()
	{
		return 'entryIndex';
	}
}

