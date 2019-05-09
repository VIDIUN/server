<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunCompareCondition extends VidiunCondition
{
	/**
	 * Value to evaluate against the field and operator
	 * @var VidiunIntegerValue
	 */
	public $value;
	
	/**
	 * Comparing operator
	 * @var VidiunSearchConditionComparison
	 */
	public $comparison;
	
	private static $mapBetweenObjects = array
	(
		'comparison',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		/* @var $dbObject vCompareCondition */
		$dbObject->setValue($this->value->toObject());
			
		return parent::toObject($dbObject, $skip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vFieldMatchCondition */
		parent::doFromObject($dbObject, $responseProfile);
		
		$valueType = get_class($dbObject->getValue());
		VidiunLog::debug("Loading VidiunIntegerValue from type [$valueType]");
		switch ($valueType)
		{
			case 'vIntegerValue':
				$this->value = new VidiunIntegerValue();
				break;
				
			case 'vTimeContextField':
				$this->value = new VidiunTimeContextField();
				break;
				
			default:
				$this->value = VidiunPluginManager::loadObject('VidiunIntegerValue', $valueType);
				break;
		}
		
		if($this->value)
			$this->value->fromObject($dbObject->getValue());
	}
}
