<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunExternalMediaEntry attributes. Use VidiunExternalMediaEntryCompareAttribute enum to provide attribute name.
*/
class VidiunExternalMediaEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunExternalMediaEntryCompareAttribute
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

