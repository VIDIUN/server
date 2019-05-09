<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 * @abstract
 */
abstract class VidiunDistributionValidationError extends VidiunObject
{
	/**
	 * @var VidiunDistributionAction
	 */
	public $action;
	
	/**
	 * @var VidiunDistributionErrorType
	 */
	public $errorType;
	
	/**
	 * @var string
	 */
	public $description;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	 (
		'action',
		'errorType',
		'description',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			return new vDistributionValidationError();
			
		return parent::toObject($dbObject, $skip);
	}
}