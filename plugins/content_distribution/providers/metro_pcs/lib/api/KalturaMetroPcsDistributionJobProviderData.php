<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage api.objects
 */
class VidiunMetroPcsDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
		
	/**
	 * @var string
	 */
	public $assetLocalPaths;
	
	
	/**
	 * @var string
	 */
	public $thumbUrls;
	
	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{			   
		parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunMetroPcsDistributionProfile))
			return;
			
		$distributedFlavorIds = null;
		$distributedThumbIds = null;
			
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
						$videoAssetFilePathArray[$id] = vFileSyncUtils::getLocalFilePathForKey($syncKey, true);
					}
				}
			}						
			$this->assetLocalPaths = serialize($videoAssetFilePathArray);	
		}
		
		//thumbnails
		$thumbnails = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		if (count($thumbnails))
		{
			$thumbUrlsArray = array();
			foreach ($thumbnails as $thumb)
			{
				$thumbUrlsArray[$thumb->getId()] = self::getAssetUrl($thumb);
			}
			$this->thumbUrls = serialize($thumbUrlsArray);
		}
		
	}
		
	private static $map_between_objects = array
	(
		"assetLocalPaths",
		"thumbUrls",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	
	protected static function getAssetUrl(asset $asset)
	{
		$urlManager = DeliveryProfilePeer::getDeliveryProfile($asset->getEntryId());
		if($asset instanceof flavorAsset)
			$urlManager->initDeliveryDynamicAttributes(null, $asset);
		$url = $urlManager->getFullAssetUrl($asset);
		$url = preg_replace('/^https?:\/\//', '', $url);
		$url = 'http://' . $url . '/ext/' . $asset->getId() . '.' . $asset->getFileExt(); 
		return $url;
	}
	
}
