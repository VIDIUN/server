<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.enum
 */
class ContentDistributionObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
	const CONTENT_DISTRIBUTION = 'ContentDistribution';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() 
	{
		return array
		(
			'CONTENT_DISTRIBUTION' => self::CONTENT_DISTRIBUTION,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}