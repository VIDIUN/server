<?php
/**
 * @package plugins.metadata
 *  @subpackage model.enum
 */
class MetadataObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
	const CUSTOM_DATA = 'CustomData';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() 
	{
		return array
		(
			'CUSTOM_DATA' => self::CUSTOM_DATA,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}