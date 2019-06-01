<?php

/**
 * Define client request optional configurations
 */
class VidiunRequestConfiguration extends VidiunObject
{
	/**
	 * Impersonated partner id
	 * @var int
	 */
	public $partnerId;
	
	/**
	 * Vidiun API session
	 * @alias sessionId
	 * @var string
	 */
	public $vs;
	
	/**
	 * Response profile - this attribute will be automatically unset after every API call.
	 * @var VidiunBaseResponseProfile
	 * @volatile
	 */
	public $responseProfile;
}