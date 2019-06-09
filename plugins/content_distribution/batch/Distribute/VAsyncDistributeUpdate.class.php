<?php
/**
 * Distributes vidiun entries to remote destination  
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class VAsyncDistributeUpdate extends VAsyncDistribute
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DISTRIBUTION_UPDATE;
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::getDistributionEngine()
	 */
	protected function getDistributionEngine($providerType, VidiunDistributionJobData $data)
	{
		return DistributionEngine::getEngine('IDistributionEngineUpdate', $providerType, $data);
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::execute()
	 */
	protected function execute(VidiunDistributionJobData $data)
	{
		return $this->engine->update($data);
	}
}
