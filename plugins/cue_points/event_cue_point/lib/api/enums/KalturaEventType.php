<?php
/**
 * @package plugins.eventCuePoint
 * @subpackage api.enum
 */
class VidiunEventType extends VidiunDynamicEnum implements EventType
{
	public static function getEnumClass()
	{
		return 'EventType';
	}
}