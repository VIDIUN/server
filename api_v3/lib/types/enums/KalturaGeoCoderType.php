<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunGeoCoderType extends VidiunDynamicEnum implements geoCoderType
{
	public static function getEnumClass()
	{
		return 'geoCoderType';
	}
}