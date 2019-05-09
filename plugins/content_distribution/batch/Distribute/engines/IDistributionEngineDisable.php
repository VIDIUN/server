<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineDisable extends IDistributionEngineUpdate
{
	/**
	 * disables the package.
	 * @param VidiunDistributionDisableJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function disable(VidiunDistributionDisableJobData $data);
}