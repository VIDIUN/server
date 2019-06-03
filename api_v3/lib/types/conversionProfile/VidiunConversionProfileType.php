<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunConversionProfileType extends VidiunDynamicEnum implements ConversionProfileType
{
	public static function getEnumClass()
	{
		return 'ConversionProfileType';
	}
}
