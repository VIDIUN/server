<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectFilterEngine
 */
class VObjectFilterEngineFactory
{
	/**
	 * @param $type
	 * @param VidiunClient $client
	 * @return VObjectFilterEngineBase
	 */
	public static function getInstanceByType($type, VidiunClient $client)
	{
		switch($type)
		{
			case VidiunObjectFilterEngineType::ENTRY:
				return new VObjectFilterBaseEntryEngine($client);
			case VidiunObjectFilterEngineType::ENTRY_VENDOR_TASK:
				return new VObjectFilterEntryVendorTaskEngine($client);
			default:
				return VidiunPluginManager::loadObject('VObjectFilterEngineBase', $type, array($client));
		}
	}
} 