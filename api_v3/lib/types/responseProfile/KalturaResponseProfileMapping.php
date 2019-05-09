<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunResponseProfileMapping extends VidiunObject
{
	/**
	 * @var string
	 */
	public $parentProperty;
	
	/**
	 * @var string
	 */
	public $filterProperty;
	
	/**
	 * @var bool
	 */
	public $allowNull;
	
	private static $map_between_objects = array(
		'parentProperty', 
		'filterProperty', 
		'allowNull', 
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyNotNull(array(
			'parentProperty', 
			'filterProperty', 
		));
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object = null, $propertiesToSkip = array())
	{
		if(is_null($object))
		{
			$object = new vResponseProfileMapping();
		}
		
		return parent::toObject($object, $propertiesToSkip);
	}
	
	/**
	 * @param VidiunRelatedFilter $filter
	 * @param VidiunObject $parentObject
	 * @return boolean
	 * @throws VidiunAPIException
	 */
	public function apply(VidiunRelatedFilter $filter, VidiunObject $parentObject)
	{
		$filterProperty = $this->filterProperty;
		$parentProperty = $this->parentProperty;
	
		VidiunLog::debug("Mapping " . get_class($parentObject) . "::{$parentProperty}[{$parentObject->$parentProperty}] to " . get_class($filter) . "::$filterProperty");
	
		if(!property_exists($parentObject, $parentProperty))
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_IS_NOT_DEFINED, $parentProperty, get_class($parentObject));
		}
		
		if(!property_exists($filter, $filterProperty))
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_IS_NOT_DEFINED, $filterProperty, get_class($filter));
		}
		
		if(is_null($parentObject->$parentProperty) && !$this->allowNull)
		{
			VidiunLog::warning("Parent property [" . get_class($parentObject) . "::{$parentProperty}] is null");
			return false;
		}
		
		$filter->$filterProperty = $parentObject->$parentProperty;
		return true;
	}
}