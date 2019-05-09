<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveChannel attributes. Use VidiunLiveChannelMatchAttribute enum to provide attribute name.
*/
class VidiunLiveChannelMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunLiveChannelMatchAttribute
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

