<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunMixEntry attributes. Use VidiunMixEntryMatchAttribute enum to provide attribute name.
*/
class VidiunMixEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunMixEntryMatchAttribute
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

