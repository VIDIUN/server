<?php
/**
 * Distributes vidiun entries to remote destination  
 *
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
class VAsyncDistributeDelete extends VAsyncDistribute
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::DISTRIBUTION_DELETE;
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::getDistributionEngine()
	 */
	protected function getDistributionEngine($providerType, VidiunDistributionJobData $data)
	{
		return DistributionEngine::getEngine('IDistributionEngineDelete', $providerType, $data);
	}
	
	/* (non-PHPdoc)
	 * @see VAsyncDistribute::execute()
	 */
	protected function execute(VidiunDistributionJobData $data)
	{
		return $this->engine->delete($data);
	}
}
