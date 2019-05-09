<?php

/**
 * @package api
 * @subpackage filters
 */
abstract class VidiunSearchComparableAttributeCondition extends VidiunAttributeCondition
{
	/**
	 * @var VidiunSearchConditionComparison
	 */
	public $comparison;

	/**
	 * Placeholder property, the real property is defined on parent classes
	 */
	protected $attribute;

	private static $mapBetweenObjects = array
	(
		'comparison',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

	public function toObject($objectToFill = null, $propsToSkip = array())
	{
		/** @var AdvancedSearchFilterComparableAttributeCondition $objectToFill */
		if (is_null($objectToFill))
			$objectToFill = new AdvancedSearchFilterComparableAttributeCondition();

		$objectToFill = parent::toObject($objectToFill, $propsToSkip);

		/** @var BaseIndexObject $indexClass */
		$indexClass = $this->getIndexClass();
		$field = $indexClass::getCompareFieldByApiName($this->attribute);
		VidiunLog::debug("Mapping [$this->attribute] to [$field]");
		$objectToFill->setField($field);
		return $objectToFill;
	}

	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/** @var $srcObj AdvancedSearchFilterComparableAttributeCondition) */
		if ($this->shouldGet('attribute', $responseProfile))
		{
			/** @var BaseIndexObject $indexClass */
			$indexClass = $this->getIndexClass();
			$this->attribute = $indexClass::getApiNameByCompareField($srcObj->getField());
		}
	}
}
