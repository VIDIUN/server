<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngine
{
	/**
	 * @param VSchedularTaskConfig $taskConfig
	 */
	public function configure();
	
	/**
	 * @param VidiunClient $vidiunClient
	 */
	public function setClient();
}