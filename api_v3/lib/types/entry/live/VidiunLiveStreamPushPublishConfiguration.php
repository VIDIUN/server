<?php
/**
 * Basic push-publish configuration for Vidiun live stream entry
 * @package api
 * @subpackage objects
 *
 */
class VidiunLiveStreamPushPublishConfiguration extends VidiunObject
{
	/**
	 * @var string
	 */
	public $publishUrl;
	
	/**
	 * @var string
	 */
	public $backupPublishUrl;
	
	/**
	 * @var string
	 */
	public $port;
	
	private static $mapBetweenObjects = array
	(
		"publishUrl", "backupPublishUrl" , "port",
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
			$dbObject = new vLiveStreamPushPublishConfiguration();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
	
	public static function getInstance ($className)
	{
		switch ($className)
		{
			case 'vLiveStreamPushPublishRTMPConfiguration':
				return new VidiunLiveStreamPushPublishRTMPConfiguration();
			default:
				return new VidiunLiveStreamPushPublishConfiguration();
		}
	}
}