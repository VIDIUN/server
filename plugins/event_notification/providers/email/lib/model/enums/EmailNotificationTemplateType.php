<?php
/**
 * @package plugins.emailNotification
 * @subpackage model.enum
 */
class EmailNotificationTemplateType implements IVidiunPluginEnum, EventNotificationTemplateType
{
	const EMAIL = 'Email';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'EMAIL' => self::EMAIL,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array(
			self::EMAIL => 'Email event notification',
		);
	}
}
