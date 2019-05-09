<?php
/**
 * Core class for recipient provider which provides a dynamic list of user recipients based on filter.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationUserRecipientProvider extends vEmailNotificationRecipientProvider
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
	
	/* (non-PHPdoc)
	 * @see vEmailNotificationRecipientProvider::getScopedProviderJobData()
	 */
	public function getScopedProviderJobData(vScope $scope = null) {
		$ret = new vEmailNotificationUserRecipientJobData();

		$ret->setFilter($this->filter);
		return $ret;
	}
	


	
}