<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunMediaParserType extends VidiunDynamicEnum implements mediaParserType
{
	public static function getEnumClass()
	{
		return 'mediaParserType';
	}
}
