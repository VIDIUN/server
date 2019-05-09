<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunMediaEntry attributes. Use VidiunMediaEntryCompareAttribute enum to provide attribute name.
*/
class VidiunMediaEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunMediaEntryCompareAttribute
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

