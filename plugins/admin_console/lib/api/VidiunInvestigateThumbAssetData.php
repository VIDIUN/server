<?php
/**
 * @package plugins.adminConsole
 * @subpackage api.objects
 */
class VidiunInvestigateThumbAssetData extends VidiunObject
{
	/**
	 * @var VidiunThumbAsset
	 * @readonly
	 */
	public $thumbAsset;

	/**
	 * @var VidiunFileSyncListResponse
	 * @readonly
	 */
	public $fileSyncs;

	/**
	 * @var VidiunThumbParams
	 * @readonly
	 */
	public $thumbParams;

	/**
	 * @var VidiunThumbParamsOutputListResponse
	 * @readonly
	 */
	public $thumbParamsOutputs;
}