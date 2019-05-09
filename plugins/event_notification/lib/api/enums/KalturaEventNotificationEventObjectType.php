<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.enum
 * @see EventNotificationEventObjectType
 */
class VidiunEventNotificationEventObjectType extends VidiunDynamicEnum implements EventNotificationEventObjectType
{
	public static function getEnumClass()
	{
		return 'EventNotificationEventObjectType';
	}
}