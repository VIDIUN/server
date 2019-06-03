<?php
/**
 * @package plugins.adminConsole
 * @subpackage api.objects
 */
class VidiunInvestigateFlavorAssetData extends VidiunObject
{
	/**
	 * @var VidiunFlavorAsset
	 * @readonly
	 */
	public $flavorAsset;

	/**
	 * @var VidiunFileSyncListResponse
	 * @readonly
	 */
	public $fileSyncs;

	/**
	 * @var VidiunMediaInfoListResponse
	 * @readonly
	 */
	public $mediaInfos;

	/**
	 * @var VidiunFlavorParams
	 * @readonly
	 */
	public $flavorParams;

	/**
	 * @var VidiunFlavorParamsOutputListResponse
	 * @readonly
	 */
	public $flavorParamsOutputs;
}