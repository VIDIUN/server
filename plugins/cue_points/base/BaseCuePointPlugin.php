<?php
/**
 * @package plugins.cuePoint
 */
abstract class BaseCuePointPlugin extends VidiunPlugin
{
	/**
	 * @return array
	 */
	public static function getSubTypes()
	{
		return array();
	}

	/**
	 * @param $subType
	 * @return enum value
	 */
	public static function getSubTypeValue($subType)
	{
		return null;
	}
}