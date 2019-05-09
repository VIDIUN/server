<?php
/**
 * Core class for a provider for the recipients email notification
 *
 * @package plugins.emailNotification
 * @subpackage model
 **/
abstract class vEmailNotificationRecipientProvider
{
	
	/**
	 * This function is called when the recipient provider needs to be narrowed down using the current context
	 * @param vContext $context
	 * @return vEmailNotificationRecipientJobData
	 */
	abstract public function getScopedProviderJobData (vScope $scope = null);
}