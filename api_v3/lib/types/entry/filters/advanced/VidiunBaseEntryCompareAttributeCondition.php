<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunBaseEntry attributes. Use VidiunBaseEntryCompareAttribute enum to provide attribute name.
*/
class VidiunBaseEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunBaseEntryCompareAttribute
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

