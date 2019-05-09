<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 * @abstract
 */
abstract class VidiunConfigurableDistributionJobProviderData extends VidiunDistributionJobProviderData
{

	/**
	 * @var string serialized array of field values
	 */
	public $fieldValues;
	
	
	private static $map_between_objects = array
	(
	    "fieldValues",
	);
    
    
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
		
	    if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunConfigurableDistributionProfile))
			return;
			
		$entryDistributionDb = EntryDistributionPeer::retrieveByPK($distributionJobData->entryDistributionId);
		$dbDistributionProfile = DistributionProfilePeer::retrieveByPK($distributionJobData->distributionProfile->id);
		if (!$entryDistributionDb) {
		    VidiunLog::err('Cannot get entry distribution id ['.$distributionJobData->entryDistributionId.']');
		    return;
		}
		if (!$dbDistributionProfile) {
		    VidiunLog::err('Cannot get distribution profile id ['.$distributionJobData->distributionProfile->id.']');
		    return;
		}
		
		$tempFieldValues = $dbDistributionProfile->getAllFieldValues($entryDistributionDb);
		if (!$tempFieldValues || !is_array($tempFieldValues)) {
		    VidiunLog::err('Error getting field values from entry distribution id ['.$entryDistributionDb->getId().'] profile id ['.$dbDistributionProfile->getId().']');
		    $tempFieldValues = array();
		}
		$this->fieldValues = serialize($tempFieldValues);
	}
	
	
}
