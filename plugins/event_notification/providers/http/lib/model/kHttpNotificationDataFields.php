<?php
/**
 * @package plugins.httpNotification
 * @subpackage model.data
 */
class vHttpNotificationDataFields extends vHttpNotificationData
{
	/**
	 * Contains the calculated data to be sent
	 * @var string
	 */
	protected $data;
	
	/* (non-PHPdoc)
	 * @see vHttpNotificationData::setScope()
	 */
	public function setScope(vScope $scope)
	{
		$this->data = http_build_query($scope->getDynamicValues());
	}
	
	/**
	 * Returns the calculated data
	 * @return string
	 */
	public function getData() 
	{
		return $this->data;
	}	
}