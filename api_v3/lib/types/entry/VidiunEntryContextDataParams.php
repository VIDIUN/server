<?php
/**
 * Object which contains contextual entry-related data.
 * @package api
 * @subpackage objects
 */
class VidiunEntryContextDataParams extends VidiunAccessControlScope
{
	/**
	 * Id of the current flavor.
	 * @var string
	 */
	public $flavorAssetId;
	
	/**
	 * The tags of the flavors that should be used for playback.
	 * @var string
	 */
	public $flavorTags;
	
	/**
	 * Playback streamer type: RTMP, HTTP, appleHttps, rtsp, sl.
	 * @var string
	 */
	public $streamerType;
	
	/**
	 * Protocol of the specific media object.
	 * @var string
	 */
	public $mediaProtocol;
}