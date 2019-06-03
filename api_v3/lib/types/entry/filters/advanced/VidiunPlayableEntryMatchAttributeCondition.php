<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunPlayableEntry attributes. Use VidiunPlayableEntryMatchAttribute enum to provide attribute name.
*/
class VidiunPlayableEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunPlayableEntryMatchAttribute
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

