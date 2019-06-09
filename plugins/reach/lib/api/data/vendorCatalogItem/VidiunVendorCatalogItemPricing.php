<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */
class VidiunVendorCatalogItemPricing extends VidiunObject
{
	/**
	 * @var float
	 */
	public $pricePerUnit;
	
	/**
	 * @var VidiunVendorCatalogItemPriceFunction
	 */
	public $priceFunction;
	
	private static $map_between_objects = array
	(
		'pricePerUnit',
		'priceFunction',
	);
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::getMapBetweenObjects()
 	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vVendorCatalogItemPricing();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
}