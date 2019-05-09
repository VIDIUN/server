<?php
/**
 * @package plugins.crossVidiunDistribution
 * @subpackage api.objects
 */
class VidiunCrossVidiunDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
    /**
     * Key-value array where the keys are IDs of distributed flavor assets in the source account and the values are the matching IDs in the target account
     * @var string
     */
    public $distributedFlavorAssets;
    
    /**
     * Key-value array where the keys are IDs of distributed thumb assets in the source account and the values are the matching IDs in the target account
     * @var string
     */
    public $distributedThumbAssets;
    
    /**
     * Key-value array where the keys are IDs of distributed metadata objects in the source account and the values are the matching IDs in the target account
     * @var string
     */
    public $distributedMetadata;
    
    /**
     * Key-value array where the keys are IDs of distributed caption assets in the source account and the values are the matching IDs in the target account
     * @var string
     */
    public $distributedCaptionAssets;
    
    /**
     * Key-value array where the keys are IDs of distributed cue points in the source account and the values are the matching IDs in the target account
     * @var string
     */
    public $distributedCuePoints;

	/**
	 * Key-value array where the keys are IDs of distributed thumb cue points in the source account and the values are the matching IDs in the target account
	 * @var string
	 */
	public $distributedThumbCuePoints;

	/**
	 * Key-value array where the keys are IDs of distributed timed thumb assets in the source account and the values are the matching IDs in the target account
	 * @var string
	 */
	public $distributedTimedThumbAssets;
    
    public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{			   
		parent::__construct($distributionJobData);
	    
		if (!$distributionJobData) {
			return;
		}
			
		if (!($distributionJobData->distributionProfile instanceof VidiunCrossVidiunDistributionProfile)) {
			return;
		}
					
		// load previously distributed data from entry distribution	
		$entryDistributionDb = EntryDistributionPeer::retrieveByPK($distributionJobData->entryDistributionId);
		if (!$entryDistributionDb)
		{
		    VidiunLog::err('Entry distribution ['.$distributionJobData->entryDistributionId.'] not found');
		    return;
		}
		
		$this->distributedFlavorAssets = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_FLAVOR_ASSETS);
		$this->distributedThumbAssets = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_THUMB_ASSETS);
		$this->distributedMetadata = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_METADATA);
		$this->distributedCaptionAssets = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_CAPTION_ASSETS);
		$this->distributedCuePoints = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_CUE_POINTS);
		$this->distributedThumbCuePoints = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_THUMB_CUE_POINTS);
		$this->distributedTimedThumbAssets = $entryDistributionDb->getFromCustomData(CrossVidiunDistributionCustomDataField::DISTRIBUTED_TIMED_THUMB_ASSETS);
	}
	
	
    private static $map_between_objects = array
	(
		'distributedFlavorAssets',
		'distributedThumbAssets',
		'distributedMetadata',
		'distributedCaptionAssets',
    	'distributedCuePoints',
    	'distributedThumbCuePoints',
	    'distributedTimedThumbAssets',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new kCrossVidiunDistributionJobProviderData();
			
		return parent::toObject($dbObject, $skip);
	}
    
}
