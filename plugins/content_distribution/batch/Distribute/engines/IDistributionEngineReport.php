<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineReport extends IDistributionEngine
{
	/**
	 * retrieves statistics.
	 * @param VidiunDistributionFetchReportJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data);
}