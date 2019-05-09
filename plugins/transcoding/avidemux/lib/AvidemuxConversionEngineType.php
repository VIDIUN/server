<?php
/**
 * @package plugins.avidemux
 * @subpackage lib
 */
class AvidemuxConversionEngineType implements conversionEngineType, IVidiunPluginEnum
{
	const AVIDEMUX = 'Avidemux';
	
	public static function getAdditionalValues()
	{
		return array(
			'AVIDEMUX' => self::AVIDEMUX
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
