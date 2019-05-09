<?php
/**
 * @package plugins.verizonVcastDistribution
 * @subpackage api.objects
 */
class VidiunVerizonVcastDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;

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
			
		if(!($distributionJobData->distributionProfile instanceof VidiunVerizonVcastDistributionProfile))
			return;

		// loads all the flavor assets that should be submitted to the remote destination site
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		$verizonFeed = new VerizonVcastFeedHelper('verizon_vcast_template.xml', $distributionJobData, $this, $flavorAssets,$thumbAssets);
		$this->xml = $verizonFeed->getXml();
		
		// save the flavors & their versions that we are sending
		$distributionJobData->mediaFiles = new VidiunDistributionRemoteMediaFileArray();
		foreach($flavorAssets as $flavorAsset)
		{
			$mediaFile = new VidiunDistributionRemoteMediaFile();
			$mediaFile->assetId = $flavorAsset->getId();
			$mediaFile->version = $flavorAsset->getVersion();
			$distributionJobData->mediaFiles[] = $mediaFile;
		}
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
