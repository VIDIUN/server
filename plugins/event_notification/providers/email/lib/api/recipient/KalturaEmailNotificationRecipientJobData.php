<?php
/**
 * Abstract class representing the final output recipients going into the batch mechanism
 * @package plugins.emailNotification
 * @subpackage model.data
 */
abstract class VidiunEmailNotificationRecipientJobData extends VidiunObject
{
	 /**
	  * Provider type of the job data.
	  * @var VidiunEmailNotificationRecipientProviderType
	  * 
	  * @readonly
	  */
	 public $providerType;
	 
	/**
	 * Protected setter to set the provider type of the job data
	 */
	abstract protected function setProviderType ();
	
	/**
	 * Function returns correct API recipient data type based on the DB class received.
	 * @param vEmailNotificationRecipientJobData $dbData
	 * @return Vidiun
	 */
	public static function getDataInstance ($dbData)
	{
		$instance = null;
		if ($dbData)
		{
			switch (get_class($dbData))
			{
				case 'vEmailNotificationCategoryRecipientJobData':
					$instance = new VidiunEmailNotificationCategoryRecipientJobData();
					break;
				case 'vEmailNotificationStaticRecipientJobData':
					$instance = new VidiunEmailNotificationStaticRecipientJobData();
					break;
				case 'vEmailNotificationUserRecipientJobData':
					$instance = new VidiunEmailNotificationUserRecipientJobData();
					break;
				case 'vEmailNotificationGroupRecipientJobData':
					$instance = new VidiunEmailNotificationGroupRecipientJobData();
					break;
				default:
					$instance = VidiunPluginManager::loadObject('VidiunEmailNotificationRecipientJobData', $dbData->getProviderType());
					break;
			}
			
			if ($instance)
				$instance->fromObject($dbData);
		}
			
		return $instance;
		
	}
}