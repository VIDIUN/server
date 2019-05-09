<?php
/**
 * Sending beacons on various objects
 * @package plugins.confMaps
 */
class ConfMapsPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunAdminConsolePages
{
	const PLUGIN_NAME = 'confMaps';

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return ($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID);
	}

	public static function getServicesMap ()
	{
		$map = array(
			'confMaps' => 'ConfMapsService',
		);
		return $map;
	}

	/*
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new ConfigurationMapListAction();
		$pages[] = new ConfigurationMapConfigureAction();
		return $pages;
	}
}