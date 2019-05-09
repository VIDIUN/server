<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunClippingTaskEntryServerNode extends VidiunTaskEntryServerNode
{
    /**
    * @var VidiunClipAttributes
    */
	public $clipAttributes;

	/**
	 * @var string
	 */
	public $clippedEntryId;

	/**
	 * @var string
	 */
	public $liveEntryId;

	private static $map_between_objects = array
    (
		"clipAttributes",
		"clippedEntryId",
		"liveEntryId",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			$object_to_fill = new ClippingTaskEntryServerNode();
		}
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}