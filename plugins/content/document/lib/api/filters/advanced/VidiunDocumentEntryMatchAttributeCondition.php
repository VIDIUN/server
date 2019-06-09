<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunDocumentEntry attributes. Use VidiunDocumentEntryMatchAttribute enum to provide attribute name.
*/
class VidiunDocumentEntryMatchAttributeCondition extends VidiunSearchMatchAttributeCondition
{
	/**
	 * @var VidiunDocumentEntryMatchAttribute
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

