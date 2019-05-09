<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveStreamAdminEntry attributes. Use VidiunLiveStreamAdminEntryMatchAttribute enum to provide attribute name.
*/
class VidiunLiveStreamAdminEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunLiveStreamAdminEntryMatchAttribute
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

