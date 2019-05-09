<?php

/**
 * @package plugins.reach
 * @subpackage api.objects 
 */

class VidiunCategoryEntryCondition extends VidiunCondition
{
	/**
	 * Category id to check condition for
	 *
	 * @var int
	 */
	public $categoryId;
	
	/**
	 * Minimum category user level permission to validate
	 *
	 * @var VidiunCategoryUserPermissionLevel
	 */
	public $categoryUserPermission;
	
	/**
	 * Comparing operator
	 * @var VidiunSearchConditionComparison
	 */
	public $comparison;
	
	private static $mapBetweenObjects = array
	(
		'categoryId',
		'categoryUserPermission',
		'comparison',
	);
	
	/**
	 * Init object type
	 */
	public function __construct()
	{
		$this->type = ReachPlugin::getApiValue(ReachConditionType::EVENT_CATEGORY_ENTRY);
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vCategoryEntryCondition();
		
		return parent::toObject($dbObject, $skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		
		$this->validatePropertyNotNull("categoryId");
		if($this->categoryUserPermission)
		{
			$this->validatePropertyNotNull("comparison");
		}
	}
}
