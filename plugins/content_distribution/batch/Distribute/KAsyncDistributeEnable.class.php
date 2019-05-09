<?php
/**
 * Distributes vidiun entries to remote destination  
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class VAsyncDistributeEnable extends VAsyncDistribute
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DISTRIBUTION_ENABLE;
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::getDistributionEngine()
	 */
	protected function getDistributionEngine($providerType, VidiunDistributionJobData $data)
	{
		return DistributionEngine::getEngine('IDistributionEngineEnable', $providerType, $data);
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::execute()
	 */
	protected function execute(VidiunDistributionJobData $data)
	{
		return $this->engine->enable($data);
	}
}
