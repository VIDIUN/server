<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */
class VidiunVendorAlignmentCatalogItem extends VidiunVendorCatalogItem
{
	/**
	 * @var VidiunCatalogItemLanguage
	 * @filter eq,in
	 */
	public $sourceLanguage;
	
	/**
	 * @var VidiunVendorCatalogItemOutputFormat
	 * @filter eq,in
	 */
	public $outputFormat;

	private static $map_between_objects = array
	(
		'sourceLanguage',
		'outputFormat',
	);
	
	protected function getServiceFeature()
	{
		return VidiunVendorServiceFeature::ALIGNMENT;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
 * @see VidiunObject::toInsertableObject()
 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
			$object_to_fill = new VendorAlignmentCatalogItem();
		
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull(array("sourceLanguage"));
		return parent::validateForInsert($propertiesToSkip);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(is_null($sourceObject))
		{
			$sourceObject = new VendorAlignmentCatalogItem();
		}
		
		return parent::toObject($sourceObject, $propertiesToSkip);
	}
}
