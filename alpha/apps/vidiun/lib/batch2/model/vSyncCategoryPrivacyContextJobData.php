<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vSyncCategoryPrivacyContextJobData extends vJobData
{
	/**
	 * Changed category id
	 * @var int
	 */   	
    private $categoryId;
       
	/**
	 * @return the $categoryId
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @param int $categoryId
	 */
	public function setCategoryId($categoryId) {
		$this->categoryId = $categoryId;
	}

}
