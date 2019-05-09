<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSessionInfo extends VidiunObject 
{
	/**
	 * @var string
	 * @readonly
	 */
	public $vs;

	/**
	 * @var VidiunSessionType
	 * @readonly
	 */
	public $sessionType;

	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;

	/**
	 * @var string
	 * @readonly
	 */
	public $userId;

	/**
	 * @var int expiry time in seconds (unix timestamp)
	 * @readonly
	 */
	public $expiry;

	/**
	 * @var string
	 * @readonly
	 */
	public $privileges;
}
