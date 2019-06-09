<?php
/**
 * A representation of an RTMP live stream configuration
 * 
 * @package api
 * @subpackage objects
 */
class VidiunLiveStreamPushPublishRTMPConfiguration extends VidiunLiveStreamPushPublishConfiguration
{
	/**
	 * @var string
	 */
	public $userId;
	
	/**
	 * @var string
	 */
	public $password;
	
	/**
	 * @var string
	 */
	public $streamName;
	
	/**
	 * @var string
	 */
	public $applicationName;
	
	private static $mapBetweenObjects = array
	(
		"userId", "password", "streamName", "applicationName"
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
			$dbObject = new vLiveStreamPushPublishRTMPConfiguration();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
}