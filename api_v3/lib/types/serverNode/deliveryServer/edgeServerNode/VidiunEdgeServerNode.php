<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunEdgeServerNode extends VidiunDeliveryServerNode
{
	/**
	 * Delivery server playback Domain
	 *
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $playbackDomain;
	
	private static $map_between_objects = array
	(
		"playbackDomain" => "playbackHostName",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		return parent::validateForInsertByType($propertiesToSkip, serverNodeType::EDGE);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		return parent::validateForUpdateByType($sourceObject, $propertiesToSkip, serverNodeType::EDGE);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new EdgeServerNode();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
}