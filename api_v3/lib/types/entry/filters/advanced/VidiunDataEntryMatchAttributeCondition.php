<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunDataEntry attributes. Use VidiunDataEntryMatchAttribute enum to provide attribute name.
*/
class VidiunDataEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunDataEntryMatchAttribute
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

