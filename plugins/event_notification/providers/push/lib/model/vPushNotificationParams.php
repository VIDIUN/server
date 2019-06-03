<?php
/**
 * @package plugins.pushNotification
 * @subpackage model
 */
class vPushNotificationParams extends VidiunObject
{
	/**
	 * @var array<vPushEventNotificationParameter>
	 */
	public $userParams;

	/**
	 * @return array<vPushEventNotificationParameter>
	 */
	public function getUserParams()
	{
		return $this->userParams;
	}

	/**
	 * @param array <vPushEventNotificationParameter> $userParams
	 */
	public function setUserParams($userParams)
	{
		$this->userParams = $userParams;
	}
}