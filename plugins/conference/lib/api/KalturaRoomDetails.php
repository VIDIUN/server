<?php
/**
* @package plugins.conference
* @subpackage api.objects
*/
class VidiunRoomDetails extends VidiunObject
{
	/**
	 * @var string
	 */
	public $serverUrl;

	/**
	 * @var string
	 */
	public $entryId;

	/**
	 * @var string
	 */
	public $token;

	/**
	 * @var int
	 */
	public $expiry;

	/**
	 * @var string
	 */
	public $serverName;

	private static $map_between_objects = array
	(
	);
	
}