<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunMediaServerNode extends VidiunDeliveryServerNode
{
	/**
	 * Media server application name
	 *
	 * @var string
	 */
	public $applicationName;
			
	/**
	 * Media server playback port configuration by protocol and format
	 *
	 * @var VidiunKeyValueArray
	 */
	public $mediaServerPortConfig;
	
	/**
	 * Media server playback Domain configuration by protocol and format
	 *
	 * @var VidiunKeyValueArray
	 * @deprecated Use Delivery Profile Ids instead
	 * 
	 */
	public $mediaServerPlaybackDomainConfig;
	
	private static $mapBetweenObjects = array
	(
		'applicationName',
		'mediaServerPortConfig',
		'mediaServerPlaybackDomainConfig',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}