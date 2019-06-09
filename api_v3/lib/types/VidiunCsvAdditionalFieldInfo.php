<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCsvAdditionalFieldInfo extends VidiunObject

{
	/**
	 * @var string
	 */
	public $fieldName;

	/**
	 * @var string
	 */
	public $xpath;


	private static $map_between_objects = array
	(
		"fieldName",
		"xpath",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/* (non-PHPdoc)
 	* @see VidiunObject::toObject()
 	*/
	public function toObject($dbAdditionalField = null, $skip = array())
	{
		if(!$dbAdditionalField)
			$dbAdditionalField = new vCsvAdditionalFieldInfo();

		return parent::toObject($dbAdditionalField, $skip);
	}

}