<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunModerationObjectType extends VidiunDynamicEnum implements moderationObjectType
{
	public static function getEnumClass()
	{
		return 'moderationObjectType';
	}
}