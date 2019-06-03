<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunPlaylist attributes. Use VidiunPlaylistMatchAttribute enum to provide attribute name.
*/
class VidiunPlaylistMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunPlaylistMatchAttribute
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

