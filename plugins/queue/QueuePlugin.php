<?php

/**
 * @package plugins.queue
 */
class QueuePlugin extends VidiunPlugin implements IVidiunVersion, IVidiunRequire
{
	const PLUGIN_NAME = 'queue';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunVersion::getVersion()
	 */
	public static function getVersion()
	{
		return new VidiunVersion(
			self::PLUGIN_VERSION_MAJOR,
			self::PLUGIN_VERSION_MINOR,
			self::PLUGIN_VERSION_BUILD
		);		
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunRequire::requires()
	 */	
	public static function requires()
	{
	    return array("IVidiunQueuePlugin");
	}
}
