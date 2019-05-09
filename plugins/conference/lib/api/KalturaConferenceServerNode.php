<?php
/**
 * @package plugins.conference
 * @subpackage api.objects
 */
class VidiunConferenceServerNode extends VidiunServerNode
{
	/**
	 * @var string
	 */
	public $serviceBaseUrl;

	private static $map_between_objects = array
	(
		"serviceBaseUrl",
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new ConferenceServerNode();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new ConferenceServerNode();
	
		return parent::toObject($dbObject, $skip);
	}
}
