<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFullStatusResponse extends VidiunObject 
{
	/**
	 * The status of all queues on the server
	 * 
	 * @var VidiunBatchQueuesStatusArray
	 */
	public $queuesStatus;
	
	
	/**
	 * Array of all schedulers
	 * 
	 * @var VidiunSchedulerArray
	 */
	public $schedulers;
}