<?php
/**
 * Core class representing the finalized implicit recipient list provider to be passed into the batch mechanism
 * 
 * @package plugins.emailNotification
 * @subpackage model.data 
 */
class vEmailNotificationUserRecipientJobData extends vEmailNotificationRecipientJobData
{
	/**
	 * @var vuserFilter
	 */
	protected $filter;
	
	/**
	 * @return vuserFilter $filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param vuserFilter $filter
	 */
	public function setFilter(vuserFilter $filter) {
		$this->filter = $filter;
	}

}