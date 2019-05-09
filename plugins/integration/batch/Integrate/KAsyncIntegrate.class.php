<?php
/**
 * @package plugins.integration
 * @subpackage Scheduler
 */
class VAsyncIntegrate extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::INTEGRATION;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->integrate($job, $job->data);
	}
	
	protected function integrate(VidiunBatchJob $job, VidiunIntegrationJobData $data)
	{
		$engine = $this->getEngine($job->jobSubType);
		if(!$engine)
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, "Engine not found", VidiunBatchJobStatus::FAILED);
		}
		
		$this->impersonate($job->partnerId);
		$finished = $engine->dispatch($job, $data);
		$this->unimpersonate();
		
		if(!$finished)
		{
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE, $data);
		}
		
		return $this->closeJob($job, null, null, "Integrated", VidiunBatchJobStatus::FINISHED, $data);
	}

	/**
	 * @param VidiunIntegrationProviderType $type
	 * @return VIntegrationEngine
	 */
	protected function getEngine($type)
	{
		return VidiunPluginManager::loadObject('VIntegrationEngine', $type);
	}
}
