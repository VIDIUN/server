<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerWorker extends VidiunObject 
{
	/**
	 * The id of the Worker
	 * 
	 * @var int
	 * @readonly
	 */
	public $id;

	
	/**
	 * The id as configured in the batch config
	 *  
	 * @var int
	 */
	public $configuredId;

	
	
	/**
	 * The id of the Scheduler
	 * 
	 * @var int
	 */
	public $schedulerId;

	
	/**
	 * The id of the scheduler as configured in the batch config
	 *  
	 * @var int
	 */
	public $schedulerConfiguredId;
	
	
	/**
	 * The worker type
	 * 
	 * @var VidiunBatchJobType
	 */
	public $type;
	
	/**
	 * The friendly name of the type
	 * 
	 * @var string
	 */
	public $typeName;
	
	
	/**
	 * The scheduler name
	 * 
	 * @var string
	 */
	public $name;


	
	/**
	 * Array of the last statuses
	 *  
	 * @var VidiunSchedulerStatusArray
	 */
	public $statuses;


	
	/**
	 * Array of the last configs
	 *  
	 * @var VidiunSchedulerConfigArray
	 */
	public $configs;


	
	/**
	 * Array of jobs that locked to this worker
	 *  
	 * @var VidiunBatchJobArray
	 */
	public $lockedJobs;


	
	/**
	 * Avarage time between creation and queue time
	 *  
	 * @var int
	 */
	public $avgWait;


	
	/**
	 * Avarage time between queue time end finish time
	 *  
	 * @var int
	 */
	public $avgWork;


	
	/**
	 * last status time
	 *  
	 * @var int
	 */
	public $lastStatus;


	
	/**
	 * last status formated
	 *  
	 * @var string
	 */
	public $lastStatusStr;

	
	private static $mapBetweenObjects = array
	(
		"id",
		"configuredId",
		"schedulerId",
		"schedulerConfiguredId",
		"type",
		"name",
		"configs",
		"lastStatus",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	    
	/**
	 * @param SchedulerWorker $dbData
	 * @return VidiunScheduler
	 */
	public function doFromObject($dbData, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbData, $responseProfile);
		
		$this->typeName = BatchJob::getTypeName($this->type);
		
		$statusesArray = $dbData->getStatuses();
		if(is_array($statusesArray))
			$this->statuses = VidiunSchedulerStatusArray::fromValuesArray($statusesArray, $this->schedulerId, $this->schedulerConfiguredId, $this->id, $this->configuredId, $this->type);
		
		$this->lastStatusStr = date('d-m-Y H:i:s', $this->lastStatus);
		
		return $this;
	}
	    
	/**
	 * @param SchedulerWorker $dbData
	 * @return VidiunScheduler
	 */
	public function statusFromObject($dbData)
	{
		$this->fromObject($dbData);
		
		$this->lockedJobs = VidiunBatchJobArray::fromBatchJobArray($dbData->getLockedJobs());
		
		$this->avgWait = BatchJobPeer::doAvgTimeDiff($this->type, BatchJobPeer::CREATED_AT, BatchJobPeer::QUEUE_TIME, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		$this->avgWork = BatchJobPeer::doAvgTimeDiff($this->type, BatchJobPeer::QUEUE_TIME, BatchJobPeer::FINISH_TIME, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
		
		return $this;
	}

	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new SchedulerWorker();
			
		if(!is_null($this->statuses) && $this->statuses instanceof VidiunSchedulerStatusArray)
			$dbData->setStatuses($this->statuses->toValuesArray());
			
		return parent::toObject($dbData, $props_to_skip);
	}
}