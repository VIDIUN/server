<?php
/**
 * @package plugins.tvinciDistribution
 * @subpackage api.objects
 */
class VidiunTvinciDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $xml;

	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);

		if( (!$distributionJobData) ||
			(!($distributionJobData->distributionProfile instanceof VidiunTvinciDistributionProfile)) ||
			(! $distributionJobData->entryDistribution) )
			return;

		$entry = null;
		if ( $distributionJobData->entryDistribution->entryId )
		{
			$entry = entryPeer::retrieveByPK($distributionJobData->entryDistribution->entryId);
		}

		if ( ! $entry ) {
			VidiunLog::err("Can't find entry with id: {$distributionJobData->entryDistribution->entryId}");
			return;
		}

		$feedHelper = new TvinciDistributionFeedHelper($distributionJobData->distributionProfile, $entry);

		if ($distributionJobData instanceof VidiunDistributionSubmitJobData)
		{
			$this->xml = $feedHelper->buildSubmitFeed();
		}
		elseif ($distributionJobData instanceof VidiunDistributionUpdateJobData)
		{
			$this->xml = $feedHelper->buildUpdateFeed();
		}
		elseif ($distributionJobData instanceof VidiunDistributionDeleteJobData)
		{
			$this->xml = $feedHelper->buildDeleteFeed();
		}
	}

	private static $map_between_objects = array
	(
		'xml',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}
