<?php

/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunAssetDistributionRule extends VidiunObject
{
	/**
	 * The validation error description that will be set on the "data" property on VidiunDistributionValidationErrorMissingAsset if rule was not fulfilled
	 *
	 * @var string
	 */
	public $validationError;

	/**
	 * An array of asset distribution conditions
	 *
	 * @var VidiunAssetDistributionConditionsArray
	 */
	public $assetDistributionConditions;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)
	*/
	private static $map_between_objects = array
	(
		'validationError',
		'assetDistributionConditions',
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vAssetDistributionRule();
			
		return parent::toObject($dbObject, $skip);
	}
}