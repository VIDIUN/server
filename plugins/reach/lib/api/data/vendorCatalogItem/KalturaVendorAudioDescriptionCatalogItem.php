<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */
class VidiunVendorAudioDescriptionCatalogItem extends VidiunVendorCatalogItem
{
	/**
	 * @var VidiunCatalogItemLanguage
	 * @filter eq,in
	 */
	public $sourceLanguage;
	
	/**
	 * @var int
	 */
	public $flavorParamsId;

	private static $map_between_objects = array
	(
		'sourceLanguage',
		'flavorParamsId',
	);
	
	protected function getServiceFeature()
	{
		return VidiunVendorServiceFeature::AUDIO_DESCRIPTION;
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
			$object_to_fill = new VendorAudioDescriptionCatalogItem();
		
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull(array("sourceLanguage"));
		$this->validatePropertyNotNull(array("flavorParamsId"));
		
		$this->validateServiceType();
		$this->validateFlavorParamsId($this->flavorParamsId);
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		/* @var $sourceObject VendorAudioDescriptionCatalogItem */
		if(isset($this->flavorParamsId) && $this->flavorParamsId != $sourceObject->getFlavorParamsId())
		{
			$this->validateFlavorParamsId($this->flavorParamsId);
		}
		
		if(isset($this->serviceType) && $this->serviceType!= $sourceObject->getServiceType())
		{
			$this->validateServiceType();
		}
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	protected function validateFlavorParamsId($id)
	{
		$flavorParams = assetParamsPeer::retrieveByPK($id);
		if (!$flavorParams)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
	}
	
	protected function validateServiceType()
	{
		if($this->serviceType != VidiunVendorServiceType::HUMAN)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_ONLY_HUMAN_ALLOWED);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(is_null($sourceObject))
		{
			$sourceObject = new VendorAudioDescriptionCatalogItem();
		}
		
		return parent::toObject($sourceObject, $propertiesToSkip);
	}
}
