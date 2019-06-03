<?php
/**
 * @package plugins.integration
 * @subpackage Scheduler
 */
interface VIntegrationCloserEngine extends VIntegrationEngine
{	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunIntegrationJobData $data
	 */
	public function close(VidiunBatchJob $job, VidiunIntegrationJobData &$data);
}
