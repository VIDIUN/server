<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunLiveStreamAdminEntry attributes. Use VidiunLiveStreamAdminEntryCompareAttribute enum to provide attribute name.
*/
class VidiunLiveStreamAdminEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunLiveStreamAdminEntryCompareAttribute
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

