<?php
/**
 * @package plugins.httpNotification
 * @subpackage model.data
 */
abstract class vHttpNotificationData
{
	/**
	 * Applies scope upon creation
	 * @param vScope $scope
	 */
	abstract public function setScope(vScope $scope);
}