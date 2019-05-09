<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerStatusResponse extends VidiunObject 
{
	/**
	 * The status of all queues on the server
	 * 
	 * @var VidiunBatchQueuesStatusArray
	 */
	public $queuesStatus;
	
	
	/**
	 * The commands that sent from the control panel
	 * 
	 * @var VidiunControlPanelCommandArray
	 */
	public $controlPanelCommands;
	
	
	/**
	 * The configuration that sent from the control panel
	 * 
	 * @var VidiunSchedulerConfigArray
	 */
	public $schedulerConfigs;
}