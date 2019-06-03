<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunMixEntry attributes. Use VidiunMixEntryCompareAttribute enum to provide attribute name.
*/
class VidiunMixEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunMixEntryCompareAttribute
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

