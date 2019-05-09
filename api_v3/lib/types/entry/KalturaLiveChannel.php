<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveChannel extends VidiunLiveEntry
{
	/**
	 * Playlist id to be played
	 * 
	 * @var string
	 */
	public $playlistId;
	
	/**
	 * Indicates that the segments should be repeated for ever
	 * @var VidiunNullableBoolean
	 */
	public $repeat;
	
	private static $map_between_objects = array
	(
		'playlistId',
		'repeat',
	);

	/* (non-PHPdoc)
	 * @see VidiunLiveEntry::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function __construct()
	{
		parent::__construct();
		
		$this->type = VidiunEntryType::LIVE_CHANNEL;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunMediaEntry::fromSourceType()
	 */
	protected function fromSourceType(entry $entry) 
	{
		$this->sourceType = VidiunSourceType::LIVE_CHANNEL;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunMediaEntry::toSourceType()
	 */
	protected function toSourceType(entry $entry) 
	{
		$entry->setSource(VidiunSourceType::LIVE_CHANNEL);
	}
}
