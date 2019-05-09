<?php
/**
 * @package plugins.yahooDistribution
 * @subpackage api.objects
 */
class VidiunYahooDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $smallThumbPath;
	
	/**
	 * @var string
	 */
	public $largeThumbPath;
	
	/**
	 * @var string
	 */
	public $videoAssetFilePath;
	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{		
	    parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunYahooDistributionProfile))
			return;
		//Flavor Assets
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		if(count($flavorAssets)) {
			$videoAssetFilePathArray = array();
			foreach ($flavorAssets as $flavorAsset)
			{
				if($flavorAsset) 
				{
					/* @var $flavorAsset flavorAsset */
					$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
					if(vFileSyncUtils::fileSync_exists($syncKey)){
						$id = $flavorAsset->getId();
					    //$this->videoAssetFilePath[$id] = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
						$videoAssetFilePathArray[$id] = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
					}
				}
			}
			$this->videoAssetFilePath = serialize($videoAssetFilePathArray);	
		}
		//Thumbnails		
		$c = new Criteria();
		$c->addAnd(assetPeer::ID, explode(',', $distributionJobData->entryDistribution->thumbAssetIds), Criteria::IN);
		$c->addAscendingOrderByColumn(assetPeer::ID);
		$thumbAssets = assetPeer::doSelect($c);		
		//$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		
		
		if(count($thumbAssets)>=2)
		{			
			if ($thumbAssets[0]->getWidth() <= $thumbAssets[1]->getWidth()){
				$smallThumbAsset = $thumbAssets[0];	
				$largeThumbAsset = $thumbAssets[1];							
			}
			else{
				$smallThumbAsset = $thumbAssets[1];	
				$largeThumbAsset = $thumbAssets[0];		
			}
			$syncKey = $smallThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey)){
			    $this->smallThumbPath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			}
			$syncKey = $largeThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey)){
			     $this->largeThumbPath= vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			}
		}	
	}
		
	private static $map_between_objects = array
	(
		"smallThumbPath",
		"largeThumbPath",
		"videoAssetFilePath",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
}
