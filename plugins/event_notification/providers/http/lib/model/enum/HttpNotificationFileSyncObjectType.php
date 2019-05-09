<?php
/**
 * @package plugins.httpNotification
 * @subpackage model.enum
 */
class HttpNotificationFileSyncObjectType implements IVidiunPluginEnum, FileSyncObjectType
{
	const HTTP_NOTIFICATION_TEMPLATE = 'HttpNotificationTemplate';
	
	public static function getAdditionalValues()
	{
		return array(
			'HTTP_NOTIFICATION_TEMPLATE' => self::HTTP_NOTIFICATION_TEMPLATE,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
