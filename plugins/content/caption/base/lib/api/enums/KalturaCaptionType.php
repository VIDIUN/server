<?php
/**
 * @package plugins.caption
 * @subpackage api.enum
 */
class VidiunCaptionType extends VidiunDynamicEnum implements CaptionType
{
	public static function getEnumClass()
	{
		return 'CaptionType';
	}
}