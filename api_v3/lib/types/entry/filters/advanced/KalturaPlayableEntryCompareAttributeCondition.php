<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunPlayableEntry attributes. Use VidiunPlayableEntryCompareAttribute enum to provide attribute name.
*/
class VidiunPlayableEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunPlayableEntryCompareAttribute
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

