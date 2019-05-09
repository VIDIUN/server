<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineDelete extends IDistributionEngine
{
	/**
	 * removes media.
	 * @param VidiunDistributionDeleteJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function delete(VidiunDistributionDeleteJobData $data);
}