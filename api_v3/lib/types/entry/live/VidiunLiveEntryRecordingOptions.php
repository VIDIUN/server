<?php
/**
 * A representation of a live stream recording entry configuration
 * 
 * @package api
 * @subpackage objects
 */
class VidiunLiveEntryRecordingOptions extends VidiunObject
{
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $shouldCopyEntitlement;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $shouldCopyScheduling;
	
	/**
	 * @var VidiunNullableBoolean
	 */
	public $shouldCopyThumbnail;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $shouldMakeHidden;

	private static $mapBetweenObjects = array
	(
		"shouldCopyEntitlement",
		"shouldCopyScheduling",
		"shouldCopyThumbnail",
		"shouldMakeHidden",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vLiveEntryRecordingOptions();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
}