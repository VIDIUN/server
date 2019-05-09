<?php
/**
 * @package plugins.booleanNotification
 * @subpackage model.enum
 */
class BooleanNotificationTemplateType implements IVidiunPluginEnum, EventNotificationTemplateType
{
	const BOOLEAN = 'Boolean';

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'BOOLEAN' => self::BOOLEAN,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions()
	{
		return array(
			self::BOOLEAN => 'Boolean event notification',
		);
	}
}