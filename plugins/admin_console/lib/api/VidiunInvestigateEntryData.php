<?php
/**
 * @package plugins.adminConsole
 * @subpackage api.objects
 */
class VidiunInvestigateEntryData extends VidiunObject
{
	/**
	 * @var VidiunBaseEntry
	 * @readonly
	 */
	public $entry;

	/**
	 * @var VidiunFileSyncListResponse
	 * @readonly
	 */
	public $fileSyncs;

	/**
	 * @var VidiunBatchJobListResponse
	 * @readonly
	 */
	public $jobs;
	
	/**
	 * @var VidiunInvestigateFlavorAssetDataArray
	 * @readonly
	 */
	public $flavorAssets;
	
	/**
	 * @var VidiunInvestigateThumbAssetDataArray
	 * @readonly
	 */
	public $thumbAssets;
	
	/**
	 * @var VidiunTrackEntryArray
	 * @readonly
	 */
	public $tracks;
}