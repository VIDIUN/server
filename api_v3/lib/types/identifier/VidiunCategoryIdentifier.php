<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCategoryIdentifier extends VidiunObjectIdentifier
{
	/**
	 * Identifier of the object
	 * @var VidiunCategoryIdentifierField
	 */
	public $identifier;
	
	/* (non-PHPdoc)
	 * @see VidiunObjectIdentifier::toObject()
	 */
	public function toObject ($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
			$dbObject = new vCategoryIdentifier();

		return parent::toObject($dbObject, $propsToSkip);
	}
	
	private static $map_between_objects = array(
			"identifier",
		);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}