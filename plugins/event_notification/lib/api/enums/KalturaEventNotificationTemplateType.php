<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.enum
 * @see EventNotificationTemplateType
 */
class VidiunEventNotificationTemplateType extends VidiunDynamicEnum implements EventNotificationTemplateType
{
	public static function getEnumClass()
	{
		return 'EventNotificationTemplateType';
	}
}