<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.enum
 * @see ObjectFilterEngineType
 */
class VidiunObjectFilterEngineType extends VidiunDynamicEnum implements ObjectFilterEngineType
{
	public static function getEnumClass()
	{
		return 'ObjectFilterEngineType';
	}
}