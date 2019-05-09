<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunModerationFlagStatus extends VidiunDynamicEnum implements moderationFlagStatus
{
	public static function getEnumClass()
	{
		return 'moderationFlagStatus';
	}
}