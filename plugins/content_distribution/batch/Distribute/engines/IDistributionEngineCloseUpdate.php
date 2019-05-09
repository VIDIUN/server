<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineCloseUpdate extends IDistributionEngine
{
	/**
	 * check for update closure in case the update is asynchronous.
	 * @param VidiunDistributionUpdateJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data);
}