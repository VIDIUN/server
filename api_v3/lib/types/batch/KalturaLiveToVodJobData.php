<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveToVodJobData extends VidiunJobData
{
	/**
	 * $vod Entry Id
	 * @var string
	 */
	public $vodEntryId;

	/**
	 * live Entry Id
	 * @var string
	 */
	public $liveEntryId;

	/**
	 * total VOD Duration
	 * @var float
	 */
	public $totalVodDuration;

	/** 
	 * last Segment Duration
	 * @var float
	 */
	public $lastSegmentDuration;
	
	/**
	 * amf Array File Path
	 * @var string
	 */
	public $amfArray;
	
	/**
	 * last live to vod sync time 
	 * @var time
	 */
	public $lastCuePointSyncTime;
	
	/**
	 * last segment drift
	 * @var int
	 */
	public $lastSegmentDrift;

	private static $map_between_objects = array
	(
		'vodEntryId',
		'liveEntryId',
		'totalVodDuration',
		'lastSegmentDuration',
		'amfArray',
		'lastCuePointSyncTime',
		'lastSegmentDrift',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vLiveToVodJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
