<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunLiveEntryFilter extends VidiunLiveEntryBaseFilter
{
	public function __construct()
	{
		$this->typeIn = VidiunEntryType::LIVE_CHANNEL . ',' . VidiunEntryType::LIVE_STREAM;
	}
	
	static private $map_between_objects = array
	(
		"isLive" => "_is_live",
		"isRecordedEntryIdEmpty" => "_is_recorded_entry_id_empty",
		"hasMediaServerHostname" => "_has_media_server_hostname",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/**
	 * @var VidiunNullableBoolean
	 */
	public $isLive;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $isRecordedEntryIdEmpty;

	/**
	 * @var string
	 */
	public $hasMediaServerHostname;
}
