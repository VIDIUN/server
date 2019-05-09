<?php
/**
 * @package plugins.drm
 * @subpackage model.enum
 */
class DrmConversionEngineType implements IVidiunPluginEnum, conversionEngineType
{
	const CENC = 'cEnc';
	
	public static function getAdditionalValues()
	{
		return array(
			'CENC' => self::CENC,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
