<?php
/**
 * @package plugins.captions
 * @subpackage model.enum
 */
class CaptionObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
	const CAPTIONS = 'Captions';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() 
	{
		return array
		(
			'CAPTIONS' => self::CAPTIONS,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}