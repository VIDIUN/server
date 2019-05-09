<?php
/**
 * @package plugins.shortLink
 */
class ShortLinkPlugin extends VidiunPlugin implements IVidiunServices, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'shortLink';
	const SHORT_LINK_FLOW_MANAGER_CLASS = 'vShortLinkFlowManager';
	
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
			'shortLink' => 'ShortLinkService',
		);
		return $map;
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::SHORT_LINK_FLOW_MANAGER_CLASS
		);
	}
}
