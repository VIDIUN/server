<?php
/**
 * @package plugins.VidiunInternalTools
 */
class VidiunInternalToolsPlugin extends VidiunPlugin implements IVidiunServices, IVidiunAdminConsolePages
{
	const PLUGIN_NAME = 'VidiunInternalTools';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'VidiunInternalTools' => 'VidiunInternalToolsService',
			'VidiunInternalToolsSystemHelper' => 'VidiunInternalToolsSystemHelperService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$VidiunInternalTools = array(new VidiunInternalToolsPluginSystemHelperAction(),new VidiunInternalToolsPluginFlavorParams());
		return $VidiunInternalTools;
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
		
		return false;
	}
}
