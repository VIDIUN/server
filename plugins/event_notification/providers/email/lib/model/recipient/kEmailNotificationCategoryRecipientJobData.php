<?php
/**
 * Class representing the finalized implicit categoryId recipient provider passed into the batch mechanism (after application of scope).
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationCategoryRecipientJobData extends vEmailNotificationRecipientJobData
{
	/**
	 * @var categoryVuserFilter
	 */
	protected $categoryUserFilter;

	/**
	 * @return categoryVuserFilter
	 */
	public function getCategoryUserFilter() {
		return $this->categoryUserFilter;
	}

	/**
	 * @param categoryVuserFilter $categoryUserFilter
	 */
	public function setCategoryUserFilter(categoryVuserFilter $categoryUserFilter) {
		$this->categoryUserFilter = $categoryUserFilter;
	}


}