<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveEntry attributes. Use VidiunLiveEntryCompareAttribute enum to provide attribute name.
*/
class VidiunLiveEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunLiveEntryCompareAttribute
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

