<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunExternalMediaEntry attributes. Use VidiunExternalMediaEntryMatchAttribute enum to provide attribute name.
*/
class VidiunExternalMediaEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunExternalMediaEntryMatchAttribute
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

