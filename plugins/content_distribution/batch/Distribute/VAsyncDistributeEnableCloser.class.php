<?php
/**
 * Distributes vidiun entries to remote destination  
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class VAsyncDistributeEnableCloser extends VAsyncDistributeCloser
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
		return DistributionEngine::getEngine('IDistributionEngineCloseUpdate', $providerType, $data);
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::execute()
	 */
	protected function execute(VidiunDistributionJobData $data)
	{
		return $this->engine->closeUpdate($data);
	}
}
