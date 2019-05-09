<?php
/**
 * @package plugins.podcastDistribution
 * @subpackage api.objects
 */
class VidiunPodcastDistributionJobProviderData extends VidiunDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;
		
	/**
	 * @var int
	 */
	public $metadataProfileId;
	
	/**
	 * @var int
	 */
	public $distributionProfileId;

	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunPodcastDistributionProfile))
			return;
	
		$this->distributionProfileId = $distributionJobData->distributionProfile->id;
	
		$this->metadataProfileId = $distributionJobData->distributionProfile->metadataProfileId;
	}
		
	private static $map_between_objects = array
	(
		"xml" ,
		"metadataProfileId" ,
		"distributionProfileId" ,
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
