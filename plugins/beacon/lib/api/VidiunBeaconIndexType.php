<?php

/**
 * @package plugins.beacon
 * @subpackage api.enum
 */
class VidiunBeaconIndexType extends VidiunStringEnum implements BeaconIndexType
{
	public static function getEnumClass()
	{
		return 'BeaconIndexType';
	}
}