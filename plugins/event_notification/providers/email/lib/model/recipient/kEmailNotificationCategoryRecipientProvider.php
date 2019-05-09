<?php
/**
 * Core class for a provider for the recipients of category-related notifications.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationCategoryRecipientProvider extends vEmailNotificationRecipientProvider
{
	/**
	 * ID of the category to whose subscribers the email should be sent
	 * @var vStringValue
	 */
	protected $categoryId;

	/**
	 * ID of the category to whose subscribers the email should be sent
	 * @var vStringValue
	 */
	protected $categoryIds;

	/**
	 * Additional filter
	 * @var categoryVuserFilter
	 */
	protected $categoryUserFilter;
	
	/**
	 * @return vStringValue
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param vStringValue $category_id
	 */
	public function setCategoryId($category_id) {
		$this->categoryId = $category_id;
	}

	/**
	 * @return vStringValue
	 */
	public function getCategoryIds() {
		return $this->categoryIds;
	}

	/**
	 * @param vStringValue $category_id
	 */
	public function setCategoryIds($category_ids) {
		$this->categoryIds = $category_ids;
	}
	
	
	/* (non-PHPdoc)
	 * @see vEmailNotificationRecipientProvider::getScopedProviderJobData()
	 */
	public function getScopedProviderJobData(vScope $scope = null)
    {
		$ret = new vEmailNotificationCategoryRecipientJobData();

		if (!$this->categoryId && !$this->categoryIds)
		{
			return $ret;
		}

		$implicitCategoryId = null;
		if ($this->categoryId && $this->categoryId instanceof vStringField)
		{
			$this->categoryId->setScope($scope);
			$implicitCategoryId = $this->categoryId->getValue();
		}

		$implicitCategoryIds = null;
		if ($this->categoryIds && $this->categoryIds instanceof vStringField)
		{

			$this->categoryIds->setScope($scope);
			$implicitCategoryIds = $this->categoryIds->getValue();
		}

		if ($implicitCategoryIds && $implicitCategoryId)
		{
			$implicitCategoryIds .= ",$implicitCategoryId";
		}

		$categoryUserFilter = new categoryVuserFilter();
		$categoryUserFilter->set('_matchor_permission_names', PermissionName::CATEGORY_SUBSCRIBE);
		if ($this->categoryUserFilter)
		{
			$categoryUserFilter = $this->categoryUserFilter;
		}

		if ($implicitCategoryIds)
		{
			$categoryUserFilter->set('_in_category_id', $implicitCategoryIds);
		}
		else
		{
			$categoryUserFilter->setCategoryIdEqual($implicitCategoryId);
		}
		$ret->setCategoryUserFilter($categoryUserFilter);
		
		return $ret;
	}

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