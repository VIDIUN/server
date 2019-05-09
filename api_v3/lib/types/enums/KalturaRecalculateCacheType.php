<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunRecalculateCacheType extends VidiunDynamicEnum implements RecalculateCacheType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'RecalculateCacheType';
	}
}
