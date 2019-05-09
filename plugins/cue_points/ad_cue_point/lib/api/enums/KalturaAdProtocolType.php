<?php
/**
 * @package plugins.adCuePoint
 * @subpackage api.enum
 */
class VidiunAdProtocolType extends VidiunDynamicEnum implements AdProtocolType
{
	public static function getEnumClass()
	{
		return 'AdProtocolType';
	}
}