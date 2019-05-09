<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveStreamEntry attributes. Use VidiunLiveStreamEntryCompareAttribute enum to provide attribute name.
*/
class VidiunLiveStreamEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunLiveStreamEntryCompareAttribute
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

