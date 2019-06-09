<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunConversionProfileStatus extends VidiunDynamicEnum implements ConversionProfileStatus
{
	public static function getEnumClass()
	{
		return 'ConversionProfileStatus';
	}
}
