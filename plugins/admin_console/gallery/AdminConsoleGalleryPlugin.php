<?php
/**
 * @package plugins.adminConsoleGallery
 */
class AdminConsoleGalleryPlugin extends VidiunPlugin implements IVidiunAdminConsolePages
{
	const PLUGIN_NAME = 'adminConsoleGallery';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		$pages[] = new AdminConsoleGalleryAction();
		return $pages;
	}
}
