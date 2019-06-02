<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAssetParamsResourceContainer extends VidiunResource 
{
	/**
	 * The content resource to associate with asset params
	 * @var VidiunContentResource
	 */
	public $resource;
	
	/**
	 * The asset params to associate with the reaource
	 * @var int
	 */
	public $assetParamsId;

	public function validateEntry(entry $dbEntry,$validateLocalExist = false)
	{
		parent::validateEntry($dbEntry, $validateLocalExist);
		$this->validatePropertyNotNull('resource');
		$this->validatePropertyNotNull('assetParamsId');

		$this->resource->validateEntry($dbEntry, $validateLocalExist);
	}
	
	public function entryHandled(entry $dbEntry)
	{
		parent::entryHandled($dbEntry);
		
    	if($this->resource)
    		$this->resource->entryHandled($dbEntry);
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vAssetParamsResourceContainer();
			
		if($this->resource)
			$object_to_fill->setResource($this->resource->toObject());
			
		$object_to_fill->setAssetParamsId($this->assetParamsId);
		return $object_to_fill;
	}
}
