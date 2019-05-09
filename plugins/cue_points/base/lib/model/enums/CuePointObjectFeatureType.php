<?php
/**
 * @package plugins.cuePoint
 * @subpackage model.enum
 */
class CuePointObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
	const CUE_POINT = 'CuePoint';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() 
	{
		return array
		(
			'CUE_POINT' => self::CUE_POINT,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}