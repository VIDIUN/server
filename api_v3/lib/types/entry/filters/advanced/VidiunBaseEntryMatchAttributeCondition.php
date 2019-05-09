<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunBaseEntry attributes. Use VidiunBaseEntryMatchAttribute enum to provide attribute name.
*/
class VidiunBaseEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunBaseEntryMatchAttribute
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

