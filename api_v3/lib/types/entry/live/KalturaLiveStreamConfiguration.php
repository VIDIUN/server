<?php
/**
 * A representation of a live stream configuration
 * 
 * @package api
 * @subpackage objects
 */
class VidiunLiveStreamConfiguration extends VidiunObject
{
	/**
	 * @var VidiunPlaybackProtocol
	 */
	public $protocol;
	
	/**
	 * @var string
	 */
	public $url;
	
	/**
	 * @var string
	 * @deprecated
	 */
	public $publishUrl;
	
	/**
	 * @var string
	 * @deprecated
	 */
	public $backupUrl;
	
	/**
	 * @var string
	 */
	public $streamName;
	
	private static $mapBetweenObjects = array
	(
		"protocol", "url", "publishUrl", "backupUrl", "streamName",
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
			$dbObject = new vLiveStreamConfiguration();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}

	
}