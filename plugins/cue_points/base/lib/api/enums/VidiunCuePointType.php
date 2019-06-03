<?php
/**
 * @package plugins.cuePoint
 * @subpackage api.enum
 */
class VidiunCuePointType extends VidiunDynamicEnum implements CuePointType
{
	public static function getEnumClass()
	{
		return 'CuePointType';
	}
}