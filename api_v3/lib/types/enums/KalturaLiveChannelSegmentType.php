<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunLiveChannelSegmentType extends VidiunDynamicEnum implements LiveChannelSegmentType
{
	public static function getEnumClass()
	{
		return 'LiveChannelSegmentType';
	}
}