<?php

/**
 * @package api
 * @subpackage filters
 */
abstract class VidiunSearchMatchAttributeCondition extends VidiunAttributeCondition
{
	/**
	 * @var bool
	 */
	public $not;

	/**
	 * Placeholder property, the real property is defined on parent classes
	 */
	protected $attribute;

	private static $mapBetweenObjects = array
	(
		'not',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

	public function toObject($objectToFill = null, $propsToSkip = array())
	{
		/** @var AdvancedSearchFilterMatchAttributeCondition $objectToFill */
		if (is_null($objectToFill))
			$objectToFill = new AdvancedSearchFilterMatchAttributeCondition();

		$objectToFill = parent::toObject($objectToFill, $propsToSkip);

		/** @var BaseIndexObject $indexClass */
		$indexClass = $this->getIndexClass();
		$field = $indexClass::getMatchFieldByApiName($this->attribute);
		VidiunLog::debug("Mapping [$this->attribute] to [$field]");
		$objectToFill->setField($field);
		return $objectToFill;
	}

	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/** @var $srcObj AdvancedSearchFilterMatchAttributeCondition) */
		if ($this->shouldGet('attribute', $responseProfile))
		{
			/** @var BaseIndexObject $indexClass */
			$indexClass = $this->getIndexClass();
			$this->attribute = $indexClass::getApiNameByMatchField($srcObj->getField());
		}
	}
}
