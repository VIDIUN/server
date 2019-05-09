<?php
/**
 * @package plugins.ftpDistribution
 * @subpackage api.objects
 */
class VidiunFtpDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var VidiunFtpDistributionFileArray
	 */
	public $filesForDistribution;
	
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
			
		if(!($distributionJobData->distributionProfile instanceof VidiunFtpDistributionProfile))
			return;
			
		$entryDistributionDb = EntryDistributionPeer::retrieveByPK($distributionJobData->entryDistributionId);
		$distributionProfileDb = DistributionProfilePeer::retrieveByPK($distributionJobData->distributionProfileId);
		
		if (is_null($entryDistributionDb))
			return VidiunLog::err('Entry distribution #'.$distributionJobData->entryDistributionId.' not found');
		
		if (is_null($distributionProfileDb))
			return VidiunLog::err('Distribution profile #'.$distributionJobData->distributionProfileId.' not found');

		if (!$distributionProfileDb instanceof FtpDistributionProfile)
			return VidiunLog::err('Distribution profile #'.$distributionJobData->distributionProfileId.' is not instance of FtpDistributionProfile');

		$this->filesForDistribution = $this->getDistributionFiles($distributionProfileDb, $entryDistributionDb);
		VidiunLog::log("Files for distribution: ".print_r($this->filesForDistribution, true));
	}
		
	/**
	 * Maps the object attributes to getters and setters for Core-to-API translation and back
	 *  
	 * @var array
	 */
	private static $map_between_objects = array
	(
		'filesForDistribution',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	protected function getDistributionFiles(FtpDistributionProfile $distributionProfileDb, EntryDistribution $entryDistributionDb)
	{
		$files = new VidiunFtpDistributionFileArray();
		$sendMetadataAfterAssets = false;
		if(!is_null($distributionProfileDb->getSendMetadataAfterAssets()))
			$sendMetadataAfterAssets = $distributionProfileDb->getSendMetadataAfterAssets();
			
		if (!$distributionProfileDb->getDisableMetadata()) 
		{
			$metadataFile = new VidiunFtpDistributionFile();
			$metadataXml = $distributionProfileDb->getMetadataXml($entryDistributionDb);
			$metadataFile->filename = $distributionProfileDb->getMetadataFilename($entryDistributionDb);
			$metadataFile->contents = $metadataXml;
			$metadataFile->assetId = 'metadata';
			$metadataFile->hash = md5($metadataXml);
			if (!$sendMetadataAfterAssets)
				$files[] = $metadataFile;
		}
		
		$flavorAssetsIds = explode(',', $entryDistributionDb->getFlavorAssetIds());
		$thumbnailAssetIds = explode(',', $entryDistributionDb->getThumbAssetIds());
		$assetIds = explode(',', $entryDistributionDb->getAssetIds());
		
		
		$assets = assetPeer::retrieveByIds(array_merge($flavorAssetsIds, $thumbnailAssetIds, $assetIds));
		
		VidiunLog::log("Assets to distribute: ".print_r($assets, true));
		
		foreach($assets as $asset) 
		{
			/* @var $assets asset */
			$syncKey = $asset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			
			$file = new VidiunFtpDistributionFile();
			$file->assetId = $asset->getId();
			
			$file->localFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			$file->version = $syncKey->getVersion();
			$defaultFilename = pathinfo($file->localFilePath, PATHINFO_BASENAME);
			
			if ($asset instanceof thumbAsset)
				$file->filename = $distributionProfileDb->getThumbnailAssetFilename($entryDistributionDb, $defaultFilename, $asset->getId());
			else if ($asset instanceof flavorAsset)
				$file->filename = $distributionProfileDb->getFlavorAssetFilename($entryDistributionDb, $defaultFilename, $asset->getId());
			else 
				$file->filename = $distributionProfileDb->getAssetFilename($entryDistributionDb, $defaultFilename, $asset->getId());
				
			$files[] = $file;
		}
		
		//sending metadata after assets as configured in the connector profile
		if ($metadataFile && $sendMetadataAfterAssets)
			$files[] = $metadataFile;
		
		return $files;
	}
}
