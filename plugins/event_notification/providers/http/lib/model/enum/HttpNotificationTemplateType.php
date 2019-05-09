<?php
/**
 * @package plugins.httpNotification
 * @subpackage model.enum
 */
class HttpNotificationTemplateType implements IVidiunPluginEnum, EventNotificationTemplateType
{
	const HTTP = 'Http';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'HTTP' => self::HTTP,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array(
			self::HTTP => 'Http event notification',
		);
	}
}
