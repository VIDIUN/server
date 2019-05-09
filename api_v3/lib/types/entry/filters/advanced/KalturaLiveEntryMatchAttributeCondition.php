<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveEntry attributes. Use VidiunLiveEntryMatchAttribute enum to provide attribute name.
*/
class VidiunLiveEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunLiveEntryMatchAttribute
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

