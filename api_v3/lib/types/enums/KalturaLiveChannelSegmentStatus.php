<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunLiveChannelSegmentStatus extends VidiunDynamicEnum implements LiveChannelSegmentStatus
{
	public static function getEnumClass()
	{
		return 'LiveChannelSegmentStatus';
	}
}