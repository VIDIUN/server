<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunConversionEngineType extends VidiunDynamicEnum implements conversionEngineType
{
	public static function getEnumClass()
	{
		return 'conversionEngineType';
	}
}
