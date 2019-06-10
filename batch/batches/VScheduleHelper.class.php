<?php
/**
 * @package Scheduler
 */

/**
 * Will import a single URL and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	parse URL	(youTube is a special case) 
 * 		fetch heraders (to calculate the size of the file)
 * 		fetch file 
 * 		move the file to the archive
 * 		set the entry's new status and file details  (check if FLV) 
 *
 * @package Scheduler
 */
class VScheduleHelper extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::SCHEDULER_HELPER;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	 */
	public function run($jobs = null)
	{
		try
		{
			$systemReady = self::$vClient->system->ping();
			if (!$systemReady) {
				VidiunLog::err("System is not yet ready - ping failed");
				return;
			}
		}
		catch (VidiunClientException $e)
		{
			VidiunLog::err("System is not yet ready - ping failed");
			return;
		}
		
		$scheduler = new VidiunScheduler();
		$scheduler->configuredId = $this->getSchedulerId();
		$scheduler->name = $this->getSchedulerName();
		$scheduler->host = VSchedulerConfig::getHostname();
		
		// get command results from the scheduler
		$commandResults = VScheduleHelperManager::loadResultsCommandsFile();
		VidiunLog::info(is_array($commandResults) ? count($commandResults) : 0 . " command results returned from the scheduler");
		if(is_array($commandResults) && count($commandResults))
			$this->sendCommandResults($commandResults);
		
		// get config from the schduler
		$configItems = VScheduleHelperManager::loadConfigItems();
		if(is_array($configItems) && count($configItems))
		{
			VidiunLog::info(count($configItems) . " config records sent from the scheduler");
			$this->sendConfigItems($scheduler, $configItems);
		}
		
		$filters = VScheduleHelperManager::loadFilters();
		VidiunLog::info(is_array($filters) ? count($filters) : 0 . " filter records found for the scheduler");
		
		// get status from the schduler
		$statuses = VScheduleHelperManager::loadStatuses();
		VidiunLog::info(is_array($statuses) ? count($statuses) : 0 . " status records sent from the scheduler");
		
		// send status to the server
		$statusResponse = self::$vClient->batchcontrol->reportStatus($scheduler, (array)$statuses, (array)$filters);
		VidiunLog::info(count($statusResponse->queuesStatus) . " queue status records returned from the server");
		VidiunLog::info(count($statusResponse->controlPanelCommands) . " control commands returned from the server");
		VidiunLog::info(count($statusResponse->schedulerConfigs) . " config items returned from the server");
		
		// send commands to the scheduler		
		$commands = array_merge($statusResponse->queuesStatus, $statusResponse->schedulerConfigs, $statusResponse->controlPanelCommands);
		VidiunLog::info(is_array($commands) ? count($commands) : 0 . " commands sent to scheduler");
		$this->saveSchedulerCommands($commands);
	}
	
	/**
	 * @param VidiunScheduler $scheduler
	 * @param array<VidiunSchedulerConfig> $configItems
	 */
	private function sendConfigItems(VidiunScheduler $scheduler, array $configItems)
	{
		$configItemsArr = array_chunk($configItems, 100);
		
		foreach($configItemsArr as $configItems)
		{
			self::$vClient->startMultiRequest();
			
			foreach($configItems as $configItem)
			{
				if($configItem instanceof VidiunSchedulerConfig)
				{
					if(is_null($configItem->value))
						$configItem->value = '';
						
					self::$vClient->batchcontrol->configLoaded($scheduler, $configItem->variable, $configItem->value, $configItem->variablePart, $configItem->workerConfiguredId, $configItem->workerName);
				}
			}
			
			self::$vClient->doMultiRequest();
		}
	}
	
	/**
	 * @param array $commandResults
	 */
	private function sendCommandResults(array $commandResults)
	{
		self::$vClient->startMultiRequest();
		
		foreach($commandResults as $commandResult)
		{
			if($commandResult instanceof VidiunSchedulerConfig)
			{
				VidiunLog::info("Handling config id[$commandResult->id], with command id[$commandResult->commandId]");
				self::$vClient->batchcontrol->setCommandResult($commandResult->commandId, $commandResult->commandStatus);
			}
			elseif($commandResult instanceof VidiunControlPanelCommand)
			{
				VidiunLog::info("Handling command id[$commandResult->id]");
				self::$vClient->batchcontrol->setCommandResult($commandResult->id, $commandResult->status, $commandResult->errorDescription);
			}
			else
			{
				VidiunLog::err(get_class($commandResult) . " object sent from scheduler");
			}
		}
		
		self::$vClient->doMultiRequest();
	}
}
