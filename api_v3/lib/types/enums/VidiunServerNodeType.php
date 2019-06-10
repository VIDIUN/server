<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunServerNodeType extends VidiunDynamicEnum implements serverNodeType
{
	public static function getEnumClass()
	{
		return 'serverNodeType';
	}
}