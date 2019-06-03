<?php
/**
 * @package plugins.integration
 * @subpackage Scheduler
 */
interface VIntegrationEngine
{	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunIntegrationJobData $data
	 */
	public function dispatch(VidiunBatchJob $job, VidiunIntegrationJobData &$data);
}
