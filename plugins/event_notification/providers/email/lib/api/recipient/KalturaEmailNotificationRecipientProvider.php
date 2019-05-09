<?php
/**
 * Abstract core class  which provides the recipients (to, CC, BCC) for an email notification
 * @package plugins.emailNotification
 * @subpackage model.data
 */
abstract class VidiunEmailNotificationRecipientProvider extends VidiunObject
{
	public static function getProviderInstance ($dbObject)
	{
		switch (get_class($dbObject))
		{
			case 'vEmailNotificationStaticRecipientProvider':
				$instance = new VidiunEmailNotificationStaticRecipientProvider();
				break;
			case 'vEmailNotificationCategoryRecipientProvider':
				$instance = new VidiunEmailNotificationCategoryRecipientProvider();
				break;
			case 'vEmailNotificationUserRecipientProvider':
				$instance = new VidiunEmailNotificationUserRecipientProvider();
				break;
			case 'vEmailNotificationGroupRecipientProvider':
				$instance = new VidiunEmailNotificationGroupRecipientProvider();
				break;
			default:
				$instance = VidiunPluginManager::loadObject('vEmailNotificationRecipientProvider', get_class($dbObject));
				break;
		}
		
		if ($instance)
			$instance->fromObject($dbObject);
		
		return $instance;
	}
}