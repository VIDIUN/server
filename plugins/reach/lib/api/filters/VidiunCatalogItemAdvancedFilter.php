<?php
/**
 * @package plugins.reach
 * @subpackage api.filters
 */
class VidiunCatalogItemAdvancedFilter extends VidiunSearchItem
{
	/**
	 * @var VidiunVendorServiceType
	 */
	public $serviceTypeEqual;
	
	/**
	 * @var string
	 */
	public $serviceTypeIn;
	
	/**
	 * @var VidiunVendorServiceFeature
	 */
	public $serviceFeatureEqual;
	
	/**
	 * @var string
	 */
	public $serviceFeatureIn;
	
	/**
	 * @var VidiunVendorServiceTurnAroundTime
	 */
	public $turnAroundTimeEqual;
	
	/**
	 * @var string
	 */
	public $turnAroundTimeIn;
	
	/**
	 * @var VidiunCatalogItemLanguage
	 */
	public $sourceLanguageEqual;
	
	/**
	 * @var VidiunCatalogItemLanguage
	 */
	public $targetLanguageEqual;
	
	
	private static $map_between_objects = array
	(
		'serviceTypeEqual',
		'serviceTypeIn',
		'serviceFeatureEqual',
		'serviceFeatureIn',
		'turnAroundTimeEqual',
		'turnAroundTimeIn',
		'sourceLanguageEqual',
		'targetLanguageEqual',
	);
	
	/* (non-PHPdoc)
 	 * @see VidiunCuePoint::getMapBetweenObjects()
 	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vCatalogItemAdvancedFilter();
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}