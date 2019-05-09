<?php
/**
 * @package plugins.pushNotification
 * @subpackage model.enum
 */
class PushNotificationTemplateType implements IVidiunPluginEnum, EventNotificationTemplateType
{
	const PUSH = 'Push';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'PUSH' => self::PUSH,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array(
			self::PUSH => 'Push event notification',
		);
	}
}
