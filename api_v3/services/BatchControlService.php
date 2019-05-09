<?php
/**
 * batch service lets you handle different batch process from remote machines.
 * As opposed to other objects in the system, locking mechanism is critical in this case.
 * For this reason the GetExclusiveXX, UpdateExclusiveXX and FreeExclusiveXX actions are important for the system's integrity.
 * In general - updating batch object should be done only using the UpdateExclusiveXX which in turn can be called only after 
 * acquiring a batch object properly (using  GetExclusiveXX).
 * If an object was acquired and should be returned to the pool in it's initial state - use the FreeExclusiveXX action 
 *
 *	Terminology:
 *		LocationId
 *		ServerID
 *		ParternGroups 
 * 
 * @service batchcontrol
 * @package api
 * @subpackage services
 */
class BatchControlService extends VidiunBaseService 
{
	// use initService to add a peer to the partner filter
	/**
	 * @ignore
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
	}
	

		
	
// --------------------------------- scheduler support functions 	--------------------------------- //

		
	
	/**
	 * batch reportStatus action saves the status attribute from a remote scheduler and returns pending commands for the scheduler
	 * 
	 * @action reportStatus
	 * @param VidiunScheduler $scheduler The scheduler
	 * @param VidiunSchedulerStatusArray $schedulerStatuses A scheduler status array
	 * @param VidiunWorkerQueueFilterArray $workerQueueFilters Filters list to get queues
	 * @return VidiunSchedulerStatusResponse
	 */
	function reportStatusAction(VidiunScheduler $scheduler, VidiunSchedulerStatusArray $schedulerStatuses, VidiunWorkerQueueFilterArray $workerQueueFilters)
	{
		$schedulerDb = $this->getOrCreateScheduler($scheduler);
		$schedulerChanged = false;
		
		// saves the statuses to the DB
		foreach($schedulerStatuses as $schedulerStatus)
		{
			$schedulerStatus->schedulerId = $schedulerDb->getId();
			$schedulerStatus->schedulerConfiguredId = $scheduler->configuredId;
			
			if($schedulerStatus->workerConfiguredId)
			{
				$worker = $this->getOrCreateWorker($schedulerDb, $schedulerStatus->workerConfiguredId, $schedulerStatus->workerType);
				$worker->setStatus($schedulerStatus->type, $schedulerStatus->value);
				$worker->save();
				$schedulerStatus->workerId = $worker->getId();
			}
			else
			{
				$schedulerChanged = true;
				$schedulerDb->setStatus($schedulerStatus->type, $schedulerStatus->value);
			}
			
			//Don't save SchedulerStatus to avoid DB insert load every couple of minutes
			//Next step would be to remove the logic that ready & writes the schedulerStatus file in VScheduleHelper & VGenericScheduler
// 			$schedulerStatusDb = new SchedulerStatus();
// 			$schedulerStatus->toObject($schedulerStatusDb);
// 			$schedulerStatusDb->save();
		}
		if($schedulerChanged)
			$schedulerDb->save();
		
		
		// creates a response
		$schedulerStatusResponse = new VidiunSchedulerStatusResponse();

		if(vConf::hasParam('batch_enable_control_panel') && vConf::get('batch_enable_control_panel'))
		{
			// gets the control pannel commands
			$c = new Criteria();
			$c->add(ControlPanelCommandPeer::SCHEDULER_ID, $schedulerDb->getId());
			$c->add(ControlPanelCommandPeer::TYPE, VidiunControlPanelCommandType::CONFIG, Criteria::NOT_EQUAL);
			$c->add(ControlPanelCommandPeer::STATUS, VidiunControlPanelCommandStatus::PENDING);
			$commandsList = ControlPanelCommandPeer::doSelect($c);
			foreach($commandsList as $command)
			{
				$command->setStatus(VidiunControlPanelCommandStatus::HANDLED);
				$command->save();
			}
			$schedulerStatusResponse->controlPanelCommands = VidiunControlPanelCommandArray::fromDbArray($commandsList, $this->getResponseProfile());
			
			// gets new configs
			$c = new Criteria();
			$c->add(SchedulerConfigPeer::SCHEDULER_ID, $schedulerDb->getId());
			$c->add(SchedulerConfigPeer::COMMAND_STATUS, VidiunControlPanelCommandStatus::PENDING);
			$configList = SchedulerConfigPeer::doSelect($c);
			foreach($configList as $config)
			{
				$config->setCommandStatus(VidiunControlPanelCommandStatus::HANDLED);
				$config->save();
			}
			$schedulerStatusResponse->schedulerConfigs = VidiunSchedulerConfigArray::fromDbArray($configList, $this->getResponseProfile());
		}
		else
		{
			$schedulerStatusResponse->controlPanelCommands = new VidiunControlPanelCommandArray();
			$schedulerStatusResponse->schedulerConfigs = new VidiunSchedulerConfigArray();
		}
		
		// gets queues length
		$schedulerStatusResponse->queuesStatus = new VidiunBatchQueuesStatusArray();
		foreach($workerQueueFilters as $workerQueueFilter)
		{
			$dbJobType = vPluginableEnumsManager::apiToCore('BatchJobType', $workerQueueFilter->jobType);
			$filter = $workerQueueFilter->filter->toFilter($dbJobType);
			
			$batchQueuesStatus = new VidiunBatchQueuesStatus();
			$batchQueuesStatus->jobType = $workerQueueFilter->jobType;
			$batchQueuesStatus->workerId = $workerQueueFilter->workerId;
			$batchQueuesStatus->size = vBatchManager::getQueueSize($workerQueueFilter->workerId, $dbJobType, $filter);
			
			$schedulerStatusResponse->queuesStatus[] = $batchQueuesStatus;
		}
		
		return $schedulerStatusResponse;
	}
	
	
	/**
	 * batch getOrCreateScheduler returns a scheduler by name, create it if doesn't exist
	 * 
	 * @param VidiunScheduler $scheduler
	 * @return Scheduler
	 */
	private function getOrCreateScheduler(VidiunScheduler $scheduler)
	{
		$c = new Criteria();
		$c->add ( SchedulerPeer::CONFIGURED_ID, $scheduler->configuredId);
		$schedulerDb = SchedulerPeer::doSelectOne($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		
		if($schedulerDb)
		{
			if(strlen($schedulerDb->getHost()) && $schedulerDb->getHost() != $scheduler->host)
				throw new VidiunAPIException(VidiunErrors::SCHEDULER_HOST_CONFLICT, $scheduler->configuredId, $scheduler->host, $schedulerDb->getHost());
			
			if($schedulerDb->getName() != $scheduler->name || $schedulerDb->getHost() != $scheduler->host)
			{
				$schedulerDb->setName($scheduler->name);
				$schedulerDb->setHost($scheduler->host);
				$schedulerDb->save();
			}
			
			return $schedulerDb;
		}
			
		$schedulerDb = new Scheduler();
		$schedulerDb->setLastStatus(time());
		$schedulerDb->setName($scheduler->name);
		$schedulerDb->setHost($scheduler->host);
		$schedulerDb->setConfiguredId($scheduler->configuredId);
		$schedulerDb->setDescription('');
		
		$schedulerDb->save();
		
		return $schedulerDb;
	}
	
	
	/**
	 * batch getOrCreateWorker returns a worker by name, create it if doesn't exist
	 * 
	 * @param Scheduler $scheduler The scheduler object
	 * @param int $workerConfigId The worker configured id
	 * @param VidiunBatchJobType $workerType The type of the remote worker
	 * @param string $workerName The name of the remote worker
	 * @return Worker
	 */
	private function getOrCreateWorker(Scheduler $scheduler, $workerConfigId, $workerType = null, $workerName = null)
	{
		if(!is_null($workerType) && !is_numeric($workerType))
			$workerType = vPluginableEnumsManager::apiToCore('BatchJobType', $workerType);
		
		$c = new Criteria();
		$c->add ( SchedulerWorkerPeer::SCHEDULER_CONFIGURED_ID, $scheduler->getConfiguredId());
		$c->add ( SchedulerWorkerPeer::CONFIGURED_ID, $workerConfigId);
		$workerDb = SchedulerWorkerPeer::doSelectOne($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		
		if($workerDb)
		{
			$shouldSave = false;
			
			if(!is_null($workerName) && $workerDb->getName() != $workerName)
			{
				$workerDb->setName($workerName);
				$shouldSave = true;
			}
			
			if(!is_null($workerType) && $workerDb->getType() != $workerType)
			{
				$workerDb->setType($workerType);
				$shouldSave = true;
			}
			
			if($shouldSave)
				$workerDb->save();
			
			return $workerDb;
		}
			
		$workerDb = new SchedulerWorker();
		$workerDb->setLastStatus(time());
		$workerDb->setCreatedBy("Scheduler: " . $scheduler->getName());
		$workerDb->setUpdatedBy("Scheduler: " . $scheduler->getName());
		$workerDb->setSchedulerId($scheduler->getId());
		$workerDb->setSchedulerConfiguredId($scheduler->getConfiguredId());
		$workerDb->setConfiguredId($workerConfigId);
		$workerDb->setDescription('');
		
		if(!is_null($workerType))
			$workerDb->setType($workerType);
		
		if(!is_null($workerName))
			$workerDb->setName($workerName);
		
		$workerDb->save();
		
		return $workerDb;
	}
	
	
	/**
	 * batch configLoaded action saves the configuration as loaded by a remote scheduler
	 * 
	 * @action configLoaded
	 * @param VidiunScheduler $scheduler The remote scheduler
	 * @param string $configParam The parameter that was loaded
	 * @param string $configValue The value that was loaded
	 * @param string $configParamPart The parameter part that was loaded
	 * @param int $workerConfigId The id of the job that the configuration refers to, not mandatory if the configuration refers to the scheduler
	 * @param string $workerName The name of the job that the configuration refers to, not mandatory if the configuration refers to the scheduler 
	 * @return VidiunSchedulerConfig
	 */
	function configLoadedAction(VidiunScheduler $scheduler, $configParam, $configValue, $configParamPart = null, $workerConfigId = null, $workerName = null)
	{
		$schedulerDb = $this->getOrCreateScheduler($scheduler);
		
		
		// saves the loaded config to the DB
		$configDb = new SchedulerConfig();
		$configDb->setSchedulerId($schedulerDb->getId());
		$configDb->setSchedulerName($scheduler->name);
		$configDb->setSchedulerConfiguredId($scheduler->configuredId);
		
		$configDb->setVariable($configParam);
		$configDb->setVariablePart($configParamPart);
		$configDb->setValue($configValue);
		
		if($workerConfigId)
		{
			$worker = $this->getOrCreateWorker($schedulerDb, $workerConfigId, null, $workerName);
			
			$configDb->setWorkerId($worker->getId());
			$configDb->setWorkerConfiguredId($workerConfigId);
			$configDb->setWorkerName($workerName);
		}
		
		$configDb->save();
		
		$config = new VidiunSchedulerConfig();
		$config->fromObject($configDb, $this->getResponseProfile());
		return $config;
	}
	
// --------------------------------- scheduler support functions 	--------------------------------- //

	
	
	
// --------------------------------- control panel functions 	--------------------------------- //

	
	/**
	 * batch stop action stops a scheduler
	 * 
	 * @action stopScheduler
	 * @param int $schedulerId The id of the remote scheduler location
	 * @param int $adminId The id of the admin that called the stop
	 * @param string $cause The reason it was stopped
	 * @return VidiunControlPanelCommand
	 */
	function stopSchedulerAction($schedulerId, $adminId, $cause)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
	
		$description = "Stop " . $schedulerDb->getName();
			
		// check if the same command already sent and not done yet
		$c = new Criteria();
		$c->add(ControlPanelCommandPeer::STATUS, array(VidiunControlPanelCommandStatus::PENDING, VidiunControlPanelCommandStatus::HANDLED), Criteria::IN);
		$c->add(ControlPanelCommandPeer::SCHEDULER_ID, $schedulerId);
		$c->add(ControlPanelCommandPeer::TYPE, VidiunControlPanelCommandType::STOP);
		$c->add(ControlPanelCommandPeer::TARGET_TYPE, VidiunControlPanelCommandTargetType::SCHEDULER);
		$commandExists = ControlPanelCommandPeer::doCount($c);
		if($commandExists > 0)
			throw new VidiunAPIException(VidiunErrors::COMMAND_ALREADY_PENDING);
		
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::STOP);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::SCHEDULER);

		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
			
		$commandDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}	
	
	/**
	 * batch stop action stops a worker
	 * 
	 * @action stopWorker
	 * @param int $workerId The id of the job to be stopped
	 * @param int $adminId The id of the admin that called the stop
	 * @param string $cause The reason it was stopped
	 * @return VidiunControlPanelCommand
	 */
	function stopWorkerAction($workerId, $adminId, $cause)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		
		$workerDb = SchedulerWorkerPeer::retrieveByPK($workerId);
		if(!$workerDb)
			throw new VidiunAPIException(VidiunErrors::WORKER_NOT_FOUND, $workerId);
		
		$workerName = $workerDb->getName();
		$schedulerId = $workerDb->getSchedulerId();
		
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
		
		$schedulerName = $schedulerDb->getName();
		$description = "Stop $workerName on $schedulerName";
			
		// check if the same command already sent and not done yet
		$c = new Criteria();
		$c->add(ControlPanelCommandPeer::STATUS, array(VidiunControlPanelCommandStatus::PENDING, VidiunControlPanelCommandStatus::HANDLED), Criteria::IN);
		$c->add(ControlPanelCommandPeer::SCHEDULER_ID, $schedulerId);
		$c->add(ControlPanelCommandPeer::TYPE, VidiunControlPanelCommandType::STOP);
		$c->add(ControlPanelCommandPeer::TARGET_TYPE, VidiunControlPanelCommandTargetType::JOB);
		$c->add(ControlPanelCommandPeer::WORKER_ID, $workerId);
		$commandExists = ControlPanelCommandPeer::doCount($c);
		if($commandExists > 0)
			throw new VidiunAPIException(VidiunErrors::COMMAND_ALREADY_PENDING);
		
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setWorkerId($workerId);
		$commandDb->setWorkerConfiguredId($workerDb->getConfiguredId());
		$commandDb->setWorkerName($workerName);
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::STOP);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::JOB);
		$commandDb->setCause($cause);

		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
				
		$commandDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}	
	
	/**
	 * batch kill action forces stop of a batch on a remote scheduler
	 * 
	 * @action kill
	 * @param int $workerId The id of the job to be stopped
	 * @param int $batchIndex The index of the batch job process to be stopped
	 * @param int $adminId The id of the admin that called the stop
	 * @param string $cause The reason it was stopped
	 * @return VidiunControlPanelCommand
	 */
	function killAction($workerId, $batchIndex, $adminId, $cause)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		
		$workerDb = SchedulerWorkerPeer::retrieveByPK($workerId);
		if(!$workerDb)
			throw new VidiunAPIException(VidiunErrors::WORKER_NOT_FOUND, $workerId);
		
		$workerName = $workerDb->getName();
		$schedulerId = $workerDb->getSchedulerId();
		
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
		
		$schedulerName = $schedulerDb->getName();
			
		$description = "Stop $workerName on $schedulerName";
		if(is_null($workerName))
			$description = "Stop $schedulerName";
			
		// check if the same command already sent and not done yet
		$c = new Criteria();
		$c->add(ControlPanelCommandPeer::STATUS, array(VidiunControlPanelCommandStatus::PENDING, VidiunControlPanelCommandStatus::HANDLED), Criteria::IN);
		$c->add(ControlPanelCommandPeer::SCHEDULER_ID, $schedulerId);
		$c->add(ControlPanelCommandPeer::WORKER_ID, $workerId);
		$c->add(ControlPanelCommandPeer::WORKER_NAME, $workerName);
		$c->add(ControlPanelCommandPeer::BATCH_INDEX, $batchIndex);
		$c->add(ControlPanelCommandPeer::TYPE, VidiunControlPanelCommandType::KILL);
		$c->add(ControlPanelCommandPeer::TARGET_TYPE, VidiunControlPanelCommandTargetType::BATCH);
		$commandExists = ControlPanelCommandPeer::doCount($c);
		if($commandExists > 0)
			throw new VidiunAPIException(VidiunErrors::COMMAND_ALREADY_PENDING);
		
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setWorkerId($workerId);
		$commandDb->setWorkerConfiguredId($workerDb->getConfiguredId());
		$commandDb->setWorkerName($workerName);
		$commandDb->setBatchIndex($batchIndex);
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::KILL);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::BATCH);
				
		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
				
		$commandDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}	
	
	/**
	 * batch start action starts a job
	 * 
	 * @action startWorker
	 * @param int $workerId The id of the job to be started
	 * @param int $adminId The id of the admin that called the start
	 * @param string $cause The reason it was started 
	 * @return VidiunControlPanelCommand
	 */
	function startWorkerAction($workerId, $adminId, $cause = null)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		
		$workerDb = SchedulerWorkerPeer::retrieveByPK($workerId);
		if(!$workerDb)
			throw new VidiunAPIException(VidiunErrors::WORKER_NOT_FOUND, $workerId);
		
		$workerName = $workerDb->getName();
		$schedulerId = $workerDb->getSchedulerId();
		
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
		
		$schedulerName = $schedulerDb->getName();
			
		$description = "Start $workerName on $schedulerName";
			
		// check if the same command already sent and not done yet
		$c = new Criteria();
		$c->add(ControlPanelCommandPeer::STATUS, array(VidiunControlPanelCommandStatus::PENDING, VidiunControlPanelCommandStatus::HANDLED), Criteria::IN);
		$c->add(ControlPanelCommandPeer::SCHEDULER_ID, $schedulerId);
		$c->add(ControlPanelCommandPeer::TYPE, VidiunControlPanelCommandType::START);
		$c->add(ControlPanelCommandPeer::TARGET_TYPE, VidiunControlPanelCommandTargetType::JOB);
		$c->add(ControlPanelCommandPeer::WORKER_ID, $workerId);
		$commandExists = ControlPanelCommandPeer::doCount($c);
		if($commandExists > 0)
			throw new VidiunAPIException(VidiunErrors::COMMAND_ALREADY_PENDING);
	
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::START);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::JOB);
		$commandDb->setWorkerId($workerId);
		$commandDb->setWorkerConfiguredId($workerDb->getConfiguredId());
		$commandDb->setWorkerName($workerName);
			
		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
				
		$commandDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}
	
	/**
	 * batch sets a configuration parameter to be loaded by a scheduler
	 * 
	 * @action setSchedulerConfig
	 * @param int $schedulerId The id of the remote scheduler location
	 * @param int $adminId The id of the admin that called the setConfig
	 * @param string $configParam The parameter to be set
	 * @param string $configValue The value to be set
	 * @param string $configParamPart The parameter part to be set - for additional params
	 * @param string $cause The reason it was changed
	 * @return VidiunControlPanelCommand
	 */
	function setSchedulerConfigAction($schedulerId, $adminId, $configParam, $configValue, $configParamPart = null, $cause = null)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
		
		$schedulerName = $schedulerDb->getName();
		
		$description = "Configure $configParam on $schedulerName";
		if(!is_null($configParamPart))
			$description = "Configure $configParam.$configParamPart on $schedulerName";
			
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::CONFIG);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::SCHEDULER);
		$commandDb->setCause($cause);
			
		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
				
		$commandDb->save();
		
		// saves the new config to the DB
		$configDb = new SchedulerConfig();
		$configDb->setSchedulerId($schedulerId);
		$configDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$configDb->setCommandId($commandDb->getId());
		$configDb->setCommandStatus(VidiunControlPanelCommandStatus::PENDING);
		$configDb->setSchedulerName($schedulerName);
		$configDb->setVariable($configParam);
		$configDb->setVariablePart($configParamPart);
		$configDb->setValue($configValue);
		
		if($adminDb)
			$configDb->setCreatedBy($adminDb->getName());
				
		$configDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}
	
	/**
	 * batch sets a configuration parameter to be loaded by a worker
	 * 
	 * @action setWorkerConfig
	 * @param int $workerId The id of the job to be configured
	 * @param int $adminId The id of the admin that called the setConfig
	 * @param string $configParam The parameter to be set
	 * @param string $configValue The value to be set
	 * @param string $configParamPart The parameter part to be set - for additional params
	 * @param string $cause The reason it was changed
	 * @return VidiunControlPanelCommand
	 */
	function setWorkerConfigAction($workerId, $adminId, $configParam, $configValue, $configParamPart = null, $cause = null)
	{
		$adminDb = vuserPeer::retrieveByPK($adminId);
		
		$workerDb = SchedulerWorkerPeer::retrieveByPK($workerId);
		if(!$workerDb)
			throw new VidiunAPIException(VidiunErrors::WORKER_NOT_FOUND, $workerId);
		
		$workerName = $workerDb->getName();
		$schedulerId = $workerDb->getSchedulerId();
		
		$schedulerDb = SchedulerPeer::retrieveByPK($schedulerId);
		if(!$schedulerDb)
			throw new VidiunAPIException(VidiunErrors::SCHEDULER_NOT_FOUND, $schedulerId);
		
		$schedulerName = $schedulerDb->getName();
		
		$description = "Configure $configParam on $schedulerName";
		if(!is_null($configParamPart))
			$description = "Configure $configParam.$configParamPart on $schedulerName";
			
		// saves the command to the DB
		$commandDb = new ControlPanelCommand();
		$commandDb->setSchedulerId($schedulerId);
		$commandDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$commandDb->setCreatedById($adminId);
		$commandDb->setType(VidiunControlPanelCommandType::CONFIG);
		$commandDb->setStatus(VidiunControlPanelCommandStatus::PENDING);
		$commandDb->setDescription($description);
		$commandDb->setWorkerId($workerId);
		$commandDb->setWorkerConfiguredId($workerDb->getConfiguredId());
		$commandDb->setWorkerName($workerName);
		$commandDb->setTargetType(VidiunControlPanelCommandTargetType::JOB);
		
		if($adminDb)
			$commandDb->setCreatedBy($adminDb->getName());
				
		$commandDb->setCause($cause);
			
		$commandDb->save();
		
		// saves the new config to the DB
		$configDb = new SchedulerConfig();
		$configDb->setSchedulerId($schedulerId);
		$configDb->setSchedulerConfiguredId($schedulerDb->getConfiguredId());
		$configDb->setCommandId($commandDb->getId());
		$configDb->setCommandStatus(VidiunControlPanelCommandStatus::PENDING);
		$configDb->setSchedulerName($schedulerName);
		$configDb->setVariable($configParam);
		$configDb->setVariablePart($configParamPart);
		$configDb->setValue($configValue);
		$configDb->setWorkerId($workerId);
		$configDb->setWorkerConfiguredId($workerDb->getConfiguredId());
		$configDb->setWorkerName($workerName);
		
		if($adminDb)
			$configDb->setCreatedBy($adminDb->getName());
		
		$configDb->save();
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}
	
	/**
	 * batch setCommandResult action saves the results of a command as received from a remote scheduler
	 * 
	 * @action setCommandResult
	 * @param int $commandId The id of the command
	 * @param VidiunControlPanelCommandStatus $status The status of the command
	 * @param int $timestamp The time that the command performed
	 * @param string $errorDescription The description, important for failed commands
	 * @return VidiunControlPanelCommand
	 */
	function setCommandResultAction($commandId, $status, $errorDescription = null)
	{
		// find the command
		$commandDb = ControlPanelCommandPeer::retrieveByPK($commandId);
		if (!$commandDb)
			throw new VidiunAPIException(VidiunErrors::COMMAND_NOT_FOUND, $commandId);
		
		// save the results to the DB
		$commandDb->setStatus($status);
		if(!is_null($errorDescription))
			$commandDb->setErrorDescription($errorDescription);
		$commandDb->save();

		// if is config, update the config status
		if($commandDb->getType() == VidiunControlPanelCommandType::CONFIG)
		{
			$c = new Criteria();
			$c->add ( SchedulerConfigPeer::COMMAND_ID, $commandId);
			$configDb = SchedulerConfigPeer::doSelectOne($c);
			
			if($configDb)
			{
				$configDb->setCommandStatus($status);
				$configDb->save();
			}
		}
		
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}

	/**
	 * list batch control commands
	 * 
	 * @action listCommands
	 * @param VidiunControlPanelCommandFilter $filter
	 * @param VidiunFilterPager $pager  
	 * @return VidiunControlPanelCommandListResponse
	 */
	function listCommandsAction(VidiunControlPanelCommandFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunControlPanelCommandFilter();
			
		$controlPanelCommandFilter = new ControlPanelCommandFilter();
		$filter->toObject($controlPanelCommandFilter);
		
		$c = new Criteria();
		
		$controlPanelCommandFilter->attachToCriteria($c);
		
		if (!$pager)
			$pager = new VidiunFilterPager ();
		
		$pager->attachToCriteria($c);
		
		$count = ControlPanelCommandPeer::doCount($c);
		$list = ControlPanelCommandPeer::doSelect($c);

		$newList = VidiunControlPanelCommandArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunControlPanelCommandListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * batch getCommand action returns the command with its current status
	 * 
	 * @action getCommand
	 * @param int $commandId The id of the command
	 * @return VidiunControlPanelCommand
	 */
	function getCommandAction($commandId)
	{
		// finds command in the DB
		$commandDb = ControlPanelCommandPeer::retrieveByPK($commandId);
		if (!$commandDb)
			throw new VidiunAPIException(VidiunErrors::COMMAND_NOT_FOUND, $commandId);
		
		// returns the command
		$command = new VidiunControlPanelCommand();
		$command->fromObject($commandDb, $this->getResponseProfile());
		return $command;
	}

	/**
	 * list all Schedulers
	 * 
	 * @action listSchedulers
	 * @return VidiunSchedulerListResponse
	 */
	function listSchedulersAction()
	{
		$c = new Criteria();
		$count = SchedulerPeer::doCount($c, false, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$list = SchedulerPeer::doSelect($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$newList = VidiunSchedulerArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunSchedulerListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		
		return $response;
	}

	/**
	 * list all Workers
	 * 
	 * @action listWorkers
	 * @return VidiunSchedulerWorkerListResponse
	 */
	function listWorkersAction()
	{
		$c = new Criteria();
		$count = SchedulerWorkerPeer::doCount($c, false, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$list = SchedulerWorkerPeer::doSelect($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$newList = VidiunSchedulerWorkerArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunSchedulerWorkerListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		
		return $response;
	}

	/**
	 * batch getFullStatus action returns the status of all schedulers and queues
	 * 
	 * @action getFullStatus
	 * @return VidiunFullStatusResponse
	 */
	function getFullStatusAction()
	{
		$response = new VidiunFullStatusResponse();
		
		// gets queues length
//		$c = new Criteria();
//		$c->add(BatchJobPeer::STATUS, array(VidiunBatchJobStatus::PENDING, VidiunBatchJobStatus::RETRY), Criteria::IN);
//		$c->addGroupByColumn(BatchJobPeer::JOB_TYPE);
//		$c->addSelectColumn('AVG(DATEDIFF(NOW(),' . BatchJobPeer::CREATED_AT . '))');
		$queueList = BatchJobPeer::doQueueStatus(myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$response->queuesStatus = VidiunBatchQueuesStatusArray::fromBatchQueuesStatusArray($queueList);
		
		$response->schedulers = VidiunSchedulerArray::statusFromSchedulerArray(SchedulerPeer::doSelect(new Criteria(), myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2)));
		
		return $response;
	}
	
// --------------------------------- control panel functions 	--------------------------------- //	
	

}
?>
