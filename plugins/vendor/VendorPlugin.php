<?php
/**
 * @package plugins.vendor
 */
class VendorPlugin extends VidiunPlugin implements  IVidiunServices
{
	const PLUGIN_NAME = 'vendor';
	const VENDOR_MANAGER = 'vVendorManager';

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}


	public static function getServicesMap()
	{
		$map = array(
			'zoomVendor' => 'ZoomVendorService',
		);
		return $map;
	}

}
