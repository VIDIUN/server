<?php
/**
 * @package plugins.emailNotification
 * @subpackage api.enum
 * @see EmailNotificationFormat
 */
class VidiunEmailNotificationFormat extends VidiunDynamicEnum implements EmailNotificationFormat
{
	public static function getEnumClass()
	{
		return 'EmailNotificationFormat';
	}
}