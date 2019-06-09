<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunContextType extends VidiunDynamicEnum implements ContextType
{
	public static function getEnumClass()
	{
		return 'ContextType';
	}
}