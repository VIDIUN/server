<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFlavorAssetWithParams extends VidiunObject
{
	/**
	 * The Flavor Asset (Can be null when there are params without asset)
	 * 
	 * @var VidiunFlavorAsset
	 */
	public $flavorAsset;
	
	/**
	 * The Flavor Params
	 * 
	 * @var VidiunFlavorParams
	 */
	public $flavorParams;
	
	/**
	 * The entry id
	 * 
	 * @var string
	 */
	public $entryId;
}