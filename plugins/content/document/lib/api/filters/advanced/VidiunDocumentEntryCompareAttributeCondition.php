<?php

/**
 * Auto-generated class.
 * 
 * Used to search VidiunDocumentEntry attributes. Use VidiunDocumentEntryCompareAttribute enum to provide attribute name.
*/
class VidiunDocumentEntryCompareAttributeCondition extends VidiunSearchComparableAttributeCondition
{
	/**
	 * @var VidiunDocumentEntryCompareAttribute
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

