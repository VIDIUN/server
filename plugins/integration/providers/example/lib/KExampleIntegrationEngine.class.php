<?php
/**
 * @package plugins.exampleIntegration
 * @subpackage Scheduler
 */
class VExampleIntegrationEngine implements VIntegrationCloserEngine
{	
	/* (non-PHPdoc)
	 * @see VIntegrationCloserEngine::dispatch()
	 */
	public function dispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data)
	{
		return $this->doDispatch($job, $data, $data->providerData);
	}

	/* (non-PHPdoc)
	 * @see VIntegrationCloserEngine::close()
	 */
	public function close(VidiunBatchJob $job, VidiunIntegrationJobData &$data)
	{
		return $this->doClose($job, $data, $data->providerData);
	}
	
	protected function doDispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunExampleIntegrationJobProviderData $providerData)
	{
		VidiunLog::debug("Example URL [$providerData->exampleUrl]");
		
		// To finish, return true
		// To wait for closer, return false
		// To fail, throw exception
		
		return false;
	}
	
	protected function doClose(VidiunBatchJob $job, VidiunIntegrationJobData &$data, VidiunExampleIntegrationJobProviderData $providerData)
	{
		VidiunLog::debug("Example URL [$providerData->exampleUrl]");
		
		// To finish, return true
		// To keep open for future closer, return false
		// To fail, throw exception
		
		return true;
	}
}
