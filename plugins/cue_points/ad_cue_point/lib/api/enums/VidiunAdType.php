<?php
/**
 * @package plugins.adCuePoint
 * @subpackage api.enum
 */
class VidiunAdType extends VidiunDynamicEnum implements AdType
{
	public static function getEnumClass()
	{
		return 'AdType';
	}
}