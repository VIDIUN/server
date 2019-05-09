<?php
/**
 * @package plugins.poll
 */
class PollPlugin extends VidiunPlugin implements IVidiunServices {

	const PLUGIN_NAME = 'poll';

	/* (non-PHPdoc)
	 * @see IVidiunServices::getPluginName()
	 */
	public static function getPluginName() {
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'poll' => 'PollService',
		);
		return $map;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}
}
