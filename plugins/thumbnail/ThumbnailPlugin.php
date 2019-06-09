<?php
/**
 * @package plugins.thumbnail
 */
class ThumbnailPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunPending
{
	const PLUGIN_NAME = 'thumbnail';

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}

	public static function dependsOn()
	{
		$dependency = new VidiunDependency(FileSyncPlugin::getPluginName());
		return array($dependency);
	}

	public static function getServicesMap ()
	{
		$map = array(
			'thumbnail' => 'ThumbnailService',
		);

		return $map;
	}
}