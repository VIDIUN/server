<?php
/**
 * @package plugins.integration
 * @subpackage Scheduler
 */
class VAsyncIntegrateCloser extends VJobCloserWorker
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
		return $this->close($job, $job->data);
	}
	
	protected function close(VidiunBatchJob $job, VidiunIntegrationJobData $data)
	{
		if(($job->queueTime + self::$taskConfig->params->maxTimeBeforeFail) < time())
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', VidiunBatchJobStatus::FAILED);
		}
		
		$engine = $this->getEngine($job->jobSubType);
		if(!$engine)
		{
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, "Engine not found", VidiunBatchJobStatus::FAILED);
		}
		
		$this->impersonate($job->partnerId);
		$finished = $engine->close($job, $data);
		$this->unimpersonate();
		
		if(!$finished)
		{
			return $this->closeJob($job, null, null, null, VidiunBatchJobStatus::ALMOST_DONE, $data);
		}
		
		return $this->closeJob($job, null, null, "Integrated", VidiunBatchJobStatus::FINISHED, $data);
	}

	/**
	 * @param VidiunIntegrationProviderType $type
	 * @return VIntegrationCloserEngine
	 */
	protected function getEngine($type)
	{
		return VidiunPluginManager::loadObject('VIntegrationCloserEngine', $type);
	}
}
