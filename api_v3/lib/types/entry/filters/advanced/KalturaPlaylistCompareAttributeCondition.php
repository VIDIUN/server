<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunPlaylist attributes. Use VidiunPlaylistCompareAttribute enum to provide attribute name.
*/
class VidiunPlaylistCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunPlaylistCompareAttribute
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

