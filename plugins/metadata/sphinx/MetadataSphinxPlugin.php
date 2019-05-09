<?php

/**
 * Enable indexing and searching of metadata objects in sphinx
 * @package plugins.metadataSphinx
 */
class MetadataSphinxPlugin extends VidiunPlugin implements IVidiunCriteriaFactory
{
	const PLUGIN_NAME = 'metadataSphinx';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCriteriaFactory::getVidiunCriteria()
	 */
	public static function getVidiunCriteria($objectType)
	{
		if ($objectType == "Metadata")
			return new SphinxMetadataCriteria();
			
		return null;
	}
}
