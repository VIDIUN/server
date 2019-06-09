<?php

/**
 * @package plugins.reach
 * @subpackage model.enum
 */

class vCategoryEntryCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ReachPlugin::getConditionTypeCoreValue(ReachConditionType::EVENT_CATEGORY_ENTRY));
		parent::__construct($not);
	}
	
	/**
	 * The categoryId to check if was added
	 *
	 * @var int
	 */
	protected $categoryId = null;
	
	/**
	 * The min category user permission level to chek for
	 *
	 * @var CategoryVuserPermissionLevel
	 */
	protected $categoryUserPermission = null;
	
	/**
	 * The min category user permission level to chek for
	 *
	 * @var searchConditionComparison
	 */
	protected $comparison = null;
	
	/**
	 * @param int categoryId
	 */
	public function setCategoryId($categoryId)
	{
		$this->categoryId = $categoryId;
	}
	
	/**
	 * @param int $categoryUserPermissionGreaterThanOrEqual
	 */
	public function setCategoryUserPermission($categoryUserPermission)
	{
		$this->categoryUserPermission = $categoryUserPermission;
	}
	
	/**
	 * @param int $searchConditionComparison
	 */
	public function setComparison($comparison)
	{
		$this->comparison = $comparison;
	}
	
	/**
	 * @return strin
	 */
	function getCategoryId()
	{
		return $this->categoryId;
	}
	
	/**
	 * @return int
	 */
	public function getCategoryUserPermission()
	{
		return $this->categoryUserPermission;
	}
	
	/**
	 * @return int
	 */
	public function getComparison()
	{
		return $this->comparison;
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		VidiunLog::debug("Validate if category added is one of the ids defined in the rule [{$this->getCategoryId()}]");
		
		$matchingCategoryEntry = null;
		$dbCategoryEntries = categoryEntryPeer::retrieveActiveByEntryId($scope->getEntryId());
		foreach($dbCategoryEntries as $dbCategoryEntry)
		{
			/* @var $dbCategoryEntry categoryEntry */
			if($dbCategoryEntry->getCategoryId() == $this->getCategoryId())
			{
				$matchingCategoryEntry = $dbCategoryEntry;
				break;
			}
		}
		
		if(!$matchingCategoryEntry)
		{
			VidiunLog::debug("No matching category entry found");
			return false;
		}
		
		$categoryUserPermission = $this->getCategoryUserPermission();
		$comparisonOperator = $this->getComparison();
		
		if(!isset($categoryUserPermission) && !isset($comparisonOperator))
		{
			//By definition if the rule does not provide comparison level and user permission than it mean that at task
			// should be created without the restriction of the user being a member of the category
			VidiunLog::debug("Comparison and permission level are not defined by rule, task should be created for all users");
			return true;
		}
		
		$dbCategoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($matchingCategoryEntry->getCategoryId(), $matchingCategoryEntry->getCreatorVuserId());
		if(!$dbCategoryVuser)
		{
			VidiunLog::debug("User [{$matchingCategoryEntry->getCreatorVuserId()}] not found in category user table");
			return false;
		}
		
		$dbUserPermission = $dbCategoryVuser->getPermissionLevel();
		switch($comparisonOperator)
		{
			case searchConditionComparison::GREATER_THAN:
				VidiunLog::debug("Compares field[$dbUserPermission] > value[$categoryUserPermission]");
				return ($dbUserPermission > $categoryUserPermission);
			
			case searchConditionComparison::GREATER_THAN_OR_EQUAL:
				VidiunLog::debug("Compares field[$dbUserPermission] >= value[$categoryUserPermission]");
				return ($dbUserPermission >= $categoryUserPermission);
			
			case searchConditionComparison::LESS_THAN:
				VidiunLog::debug("Compares field[$dbUserPermission] < value[$categoryUserPermission]");
				return ($dbUserPermission < $categoryUserPermission);
			
			case searchConditionComparison::LESS_THAN_OR_EQUAL:
				VidiunLog::debug("Compares field[$dbUserPermission] <= value[$categoryUserPermission]");
				return ($dbUserPermission <= $categoryUserPermission);
			
			case searchConditionComparison::EQUAL:
				VidiunLog::debug("Compares field[$dbUserPermission] == value[$categoryUserPermission]");
				return ($dbUserPermission == $categoryUserPermission);
		}
		
		return false;
	}
}