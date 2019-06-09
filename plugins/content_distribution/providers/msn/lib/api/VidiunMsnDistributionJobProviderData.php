<?php
/**
 * @package plugins.msnDistribution
 * @subpackage api.objects
 */
class VidiunMsnDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;
	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunMsnDistributionProfile))
			return;
			
		$flavorAssetsByMsnId = array();
		$entryId = $distributionJobData->entryDistribution->entryId;
		$distributionProfile = $distributionJobData->distributionProfile;
		/* @var $distributionProfile VidiunMsnDistributionProfile */
		$this->addFlavorByMsnId($flavorAssetsByMsnId, 1001, $entryId, $distributionProfile->sourceFlavorParamsId);
		$this->addFlavorByMsnId($flavorAssetsByMsnId, 1002, $entryId, $distributionProfile->wmvFlavorParamsId);
		$this->addFlavorByMsnId($flavorAssetsByMsnId, 1003, $entryId, $distributionProfile->flvFlavorParamsId);
		$this->addFlavorByMsnId($flavorAssetsByMsnId, 1004, $entryId, $distributionProfile->slFlavorParamsId);
		$this->addFlavorByMsnId($flavorAssetsByMsnId, 1005, $entryId, $distributionProfile->slHdFlavorParamsId);
		
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
			
		$feed = new MsnDistributionFeed($distributionJobData, $this);
		$feed->addFlavorAssetsByMsnId($flavorAssetsByMsnId);
		$feed->addThumbnailAssets($thumbAssets);
		
		if($distributionJobData instanceof VidiunDistributionSubmitJobData)
		{
			$this->xml = $feed->getXml();
		}
			
		if($distributionJobData instanceof VidiunDistributionUpdateJobData)
		{
			$feed->setUUID($distributionJobData->remoteId);
			$this->xml = $feed->getXml();
		}
		
	}
		
	private static $map_between_objects = array
	(
		"xml" ,
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	protected function addFlavorByMsnId(array &$flavorAssetsByMsnId, $msnId, $entryId, $flavorAssetId)
	{
		if ($flavorAssetId != -1)
		{
			$flavorAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $flavorAssetId);
			if($flavorAsset)
				$flavorAssetsByMsnId[$msnId] = $flavorAsset;
		}
	}
}
