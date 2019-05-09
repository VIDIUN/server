<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunMediaEntry attributes. Use VidiunMediaEntryMatchAttribute enum to provide attribute name.
*/
class VidiunMediaEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunMediaEntryMatchAttribute
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

