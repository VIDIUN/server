<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveChannel attributes. Use VidiunLiveChannelCompareAttribute enum to provide attribute name.
*/
class VidiunLiveChannelCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunLiveChannelCompareAttribute
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

