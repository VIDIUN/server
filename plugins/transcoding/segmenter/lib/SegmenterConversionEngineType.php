<?php
/**
 * @package plugins.segmenter
 * @subpackage lib
 */
class SegmenterConversionEngineType implements IVidiunPluginEnum, conversionEngineType
{
	const SEGMENTER = 'Segmenter';
	
	public static function getAdditionalValues()
	{
		return array(
			'SEGMENTER' => self::SEGMENTER
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
