<?php

/**
 * This plugin replaces the deprecated BulkUploadService. It includes a service for uploading entries, categories, users and categoryUsers in bulks.
 *@package plugins.bulkUpload
 *
 */
class BulkUploadPlugin extends VidiunPlugin implements IVidiunServices, IVidiunEventConsumers
{
    const PLUGIN_NAME = "bulkUpload";

	/* (non-PHPdoc)
     * @see IVidiunPlugin::getPluginName()
     */
    public static function getPluginName ()
    {
        return self::PLUGIN_NAME;
        
    }

    public static function getServicesMap()
	{
		$map = array(
			'bulk' => 'BulkService',
		);
		return $map;
	}

	/* (non-PHPdoc)
     * @see IVidiunEventConsumers::getEventConsumers()
     */
    public static function getEventConsumers ()
    {
        return array('vBatchJobLogManager');
    }
}
