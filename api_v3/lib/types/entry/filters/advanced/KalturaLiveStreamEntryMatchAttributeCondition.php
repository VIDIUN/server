<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveStreamEntry attributes. Use VidiunLiveStreamEntryMatchAttribute enum to provide attribute name.
*/
class VidiunLiveStreamEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunLiveStreamEntryMatchAttribute
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

