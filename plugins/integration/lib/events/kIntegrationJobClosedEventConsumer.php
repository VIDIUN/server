<?php
/**
 * @package plugins.integration
 * @subpackage lib.events
 */
interface vIntegrationJobClosedEventConsumer extends VidiunEventConsumer
{
	/**
	 * @param BatchJob $batchJob
	 */
	public function shouldConsumeIntegrationCloseEvent(BatchJob $batchJob);
	
	/**
	 * @param BatchJob $batchJob
	 */
	public function integrationJobClosed(BatchJob $batchJob);
}
