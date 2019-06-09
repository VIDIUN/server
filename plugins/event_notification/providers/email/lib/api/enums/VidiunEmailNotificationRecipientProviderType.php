<?php
/**
 * Enum class for recipient provider types
 * 
 * @package plugins.emailNotification
 * @subpackage api.enums
 */
class VidiunEmailNotificationRecipientProviderType extends VidiunDynamicEnum implements EmailNotificationRecipientProviderType 
{
	public static function getEnumClass()
	{
		return 'EmailNotificationRecipientProviderType';
	}
}