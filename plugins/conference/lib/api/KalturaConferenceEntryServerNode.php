<?php
/**
 * @package plugins.conference
 * @subpackage api.objects
 */
class VidiunConferenceEntryServerNode extends VidiunEntryServerNode
{

	/**
	 * @var VidiunConferenceRoomStatus
	 * @readonly
	 */
	public $confRoomStatus;

	/**
	 * @var int
	 * @readonly
	 */
	public $registered;

	private static $map_between_objects = array
	(
		"confRoomStatus",
		"registered",
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			$object_to_fill = new ConferenceEntryServerNode();
		}
		return parent::toObject($object_to_fill, $props_to_skip);
	}

}
