<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineCloseSubmit extends IDistributionEngine
{
	/**
	 * check for submission closure in case the submission is asynchronous.
	 * @param VidiunDistributionSubmitJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data);
}