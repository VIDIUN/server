<?php
/**
 * @package plugins.quickPlayDistribution
 * @subpackage api.objects
 */
class VidiunQuickPlayDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;
	
	/**
	 * @var VidiunStringArray
	 */
	public $videoFilePaths;

	/**
	 * @var VidiunStringArray
	 */
	public $thumbnailFilePaths;

	/**
	 * Called on the server side and enables you to populate the object with any data from the DB
	 * 
	 * @param VidiunDistributionJobData $distributionJobData
	 */
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunQuickPlayDistributionProfile))
			return;
			
		$this->videoFilePaths = new VidiunStringArray();
		$this->thumbnailFilePaths = new VidiunStringArray();

		// loads all the flavor assets that should be submitted to the remote destination site
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		$entry = entryPeer::retrieveByPK($distributionJobData->entryDistribution->entryId);
		
		foreach($flavorAssets as $asset)
		{
			$syncKey = $asset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey))
			{
				$str = new VidiunString();
				$str->value = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			    $this->videoFilePaths[] = $str;
			}
		}
		
		foreach($thumbAssets as $asset)
		{
			$syncKey = $asset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey))
			{
				$str = new VidiunString();
				$str->value = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			    $this->thumbnailFilePaths[] = $str;
			}
		}
		
		$feed = new QuickPlayFeed($distributionJobData, $this, $flavorAssets, $thumbAssets, $entry);
		$this->xml = $feed->getXml();
	}
		
	/**
	 * Maps the object attributes to getters and setters for Core-to-API translation and back
	 *  
	 * @var array
	 */
	private static $map_between_objects = array
	(
		'xml',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
