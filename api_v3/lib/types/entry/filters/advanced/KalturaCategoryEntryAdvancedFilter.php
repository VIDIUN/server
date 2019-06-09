<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunCategoryEntryAdvancedFilter extends VidiunSearchItem
{
	/**
	 * @var string
	 */
	public $categoriesMatchOr;
	
	/**
	 * @var string
	 */
	public $categoryEntryStatusIn;
	
	/**
	 * @var VidiunCategoryEntryAdvancedOrderBy
	 */
	public $orderBy;
	
	/**
	 * @var int
	 */
	public $categoryIdEqual;
	
	private static $map_between_objects = array
	(
		"categoriesMatchOr",
		"categoryEntryStatusIn",
		"orderBy",
		"categoryIdEqual",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vCategoryEntryAdvancedFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);

		$categoriesMatchOrIsNull = is_null($this->categoriesMatchOr);
		$categoryIdEqualIsNull = is_null($this->categoryIdEqual);
		$orderByIsNull = is_null( $this->orderBy );

		if ( $categoriesMatchOrIsNull && $categoryIdEqualIsNull )
		{
			// Leaving the condition here in order to emphasis that it is allowed
			// in order not to break backward-compatibility
		}
		else if ( !$categoriesMatchOrIsNull && !$categoryIdEqualIsNull )
		{
			throw new VidiunAPIException( VidiunErrors::PROPERTY_VALIDATION_ALL_MUST_BE_NULL_BUT_ONE, "categoriesMatchOr / categoryIdEqual" );
		}
		else if ( !$orderByIsNull && !$categoriesMatchOrIsNull )
		{
			// categoriesMatchOr may yield a hierarchy of category entries, thus may not be used in conjunction with orderBy
			throw new VidiunAPIException( VidiunErrors::PROPERTY_VALIDATION_ALL_MUST_BE_NULL_BUT_ONE, "categoriesMatchOr / orderBy" );
		}
	}
}
