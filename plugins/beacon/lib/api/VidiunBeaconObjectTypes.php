<?php

/**
 * @package plugins.beacon
 * @subpackage api.enum
 */
class VidiunBeaconObjectTypes extends VidiunDynamicEnum implements BeaconObjectTypes
{
	public static function getEnumClass()
	{
		return 'BeaconObjectTypes';
	}
}