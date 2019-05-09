<?php
/**
 * @package plugins.freewheelGenericDistribution
 * @subpackage api.objects
 */
class VidiunFreewheelGenericDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * Demonstrate passing array of paths to the job
	 * 
	 * @var VidiunStringArray
	 */
	public $videoAssetFilePaths;
	
	/**
	 * Demonstrate passing single path to the job
	 * 
	 * @var string
	 */
	public $thumbAssetFilePath;
	
	/**
	 * @var VidiunCuePointArray
	 */
	public $cuePoints;
	

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
			
		if(!($distributionJobData->distributionProfile instanceof VidiunFreewheelGenericDistributionProfile))
			return;
			
		$this->videoAssetFilePaths = new VidiunStringArray();
		
		// loads all the flavor assets that should be submitted to the remote destination site
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		foreach($flavorAssets as $flavorAsset)
		{
			$videoAssetFilePath = new VidiunString();
			$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			$videoAssetFilePath->value = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			$this->videoAssetFilePaths[] = $videoAssetFilePath;
		}
		
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		if(count($thumbAssets))
		{
			$thumbAsset = reset($thumbAssets);
			$syncKey = $thumbAssets->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			$this->thumbAssetFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}
		
		// entry cue points
		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$c->add(CuePointPeer::PARTNER_ID, $distributionJobData->entryDistribution->partnerId);
		$c->add(CuePointPeer::ENTRY_ID, $distributionJobData->entryDistribution->entryId);
		$c->add(CuePointPeer::TYPE, AdCuePointPlugin::getCuePointTypeCoreValue(AdCuePointType::AD));
		$c->addAscendingOrderByColumn(CuePointPeer::START_TIME);
		$cuePointsDb = CuePointPeer::doSelect($c);
		$this->cuePoints = VidiunCuePointArray::fromDbArray($cuePointsDb);
	}
		
	/**
	 * Maps the object attributes to getters and setters for Core-to-API translation and back
	 *  
	 * @var array
	 */
	private static $map_between_objects = array
	(
		"thumbAssetFilePath",
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($object = null, $skip = array())
	{
		$object = parent::toObject($object, $skip);
		
		if($this->videoAssetFilePaths)
		{
			$videoAssetFilePaths = array();
			foreach($this->videoAssetFilePaths as $videoAssetFilePath)
			{
				/* @var $videoAssetFilePath VidiunString */
				$videoAssetFilePaths[] = $videoAssetFilePath->value;
			}
				
			$object->setVideoAssetFilePaths($videoAssetFilePaths);
		}
		
		return $object;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($object, $responseProfile);
		$videoAssetFilePaths = $object->getVideoAssetFilePaths();
		if($videoAssetFilePaths && is_array($videoAssetFilePaths))
		{
			$this->videoAssetFilePaths = new VidiunStringArray();
			foreach($videoAssetFilePaths as $assetFilePath)
			{
				$videoAssetFilePath = new VidiunString();
				$videoAssetFilePath->value = $assetFilePath;
				$this->videoAssetFilePaths[] = $videoAssetFilePath;
			}
		}
	}
}
