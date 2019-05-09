<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineUpdate extends IDistributionEngine
{
	/**
	 * updates media or metadata.
	 * @param VidiunDistributionUpdateJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function update(VidiunDistributionUpdateJobData $data);
}