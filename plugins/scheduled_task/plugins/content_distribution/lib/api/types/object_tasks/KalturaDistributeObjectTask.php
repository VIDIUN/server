<?php

/**
 * @package plugins.scheduledTaskContentDistribution
 * @subpackage api.objects.objectTasks
 */
class VidiunDistributeObjectTask extends VidiunObjectTask
{
	/**
	 * Distribution profile id
	 *
	 * @var string
	 */
	public $distributionProfileId;

	public function __construct()
	{
		$this->type = ScheduledTaskContentDistributionPlugin::getApiValue(DistributeObjectTaskType::DISTRIBUTE);
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);

		$dbObject->setDataValue('distributionProfileId', $this->distributionProfileId);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->distributionProfileId = $srcObj->getDataValue('distributionProfileId');
	}
}