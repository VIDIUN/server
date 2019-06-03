<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerStatus extends VidiunObject 
{
	/**
	 * The id of the Category
	 * 
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;

	
	
	/**
	 * The configured id of the scheduler
	 *  
	 * @var int
	 */
	public $schedulerConfiguredId;


	
	/**
	 * The configured id of the job worker
	 *  
	 * @var int
	 */
	public $workerConfiguredId;


	
	/**
	 * The type of the job worker.
	 *  
	 * @var VidiunBatchJobType
	 */
	public $workerType;


	
	/**
	 * The status type
	 *  
	 * @var VidiunSchedulerStatusType
	 */
	public $type;


	
	/**
	 * The status value
	 *  
	 * @var int
	 */
	public $value;
	
	
	/**
	 * The id of the scheduler
	 * 
	 * @var int
	 * @readonly
	 */
	public $schedulerId;
	
	
	/**
	 * The id of the worker
	 * 
	 * @var int
	 * @readonly
	 */
	public $workerId;
	
	
	
	private static $mapBetweenObjects = array
	(
		"id",
		"schedulerConfiguredId",
		"workerConfiguredId",
		"workerType",
		"type",
		"value",
		"schedulerId",
		"workerId",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

	public function doFromObject($dbData, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbData, $responseProfile);
		
		$statusesArray = $dbData->getStatuses();
		if(is_array($statusesArray))
			$this->statuses = VidiunSchedulerStatusArray::fromValuesArray($statusesArray, $this->schedulerId, $this->schedulerConfiguredId, $this->id, $this->configuredId, $this->type);
		
		$this->configs = VidiunSchedulerConfigArray::fromDbArray($dbData->getConfigs());
		$this->lockedJobs = VidiunBatchJobArray::fromBatchJobArray($dbData->getLockedJobs());
		
		return $this;
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
}