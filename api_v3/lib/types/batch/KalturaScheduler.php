<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunScheduler extends VidiunObject 
{
	/**
	 * The id of the Scheduler
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
	 * The scheduler name
	 * 
	 * @var string
	 */
	public $name;

	
	/**
	 * The host name
	 * 
	 * @var string
	 */
	public $host;


	
	/**
	 * Array of the last statuses
	 *  
	 * @var VidiunSchedulerStatusArray
	 * @readonly
	 */
	public $statuses;


	
	/**
	 * Array of the last configs
	 *  
	 * @var VidiunSchedulerConfigArray
	 * @readonly
	 */
	public $configs;


	
	/**
	 * Array of the workers
	 *  
	 * @var VidiunSchedulerWorkerArray
	 * @readonly
	 */
	public $workers;


	
	/**
	 * creation time
	 *  
	 * @var time
	 * @readonly
	 */
	public $createdAt;


	
	/**
	 * last status time
	 *  
	 * @var int
	 * @readonly
	 */
	public $lastStatus;


	
	/**
	 * last status formated
	 *  
	 * @var string
	 * @readonly
	 */
	public $lastStatusStr;


	
	private static $mapBetweenObjects = array
	(
		"id",
		"configuredId",
		"name",
		"host",
		"createdAt",
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
	 * @param Scheduler $dbData
	 * @return VidiunScheduler
	 */
	public function doFromObject($dbData, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbData, $responseProfile);
		
		$statusesArray = $dbData->getStatuses();
		if(is_array($statusesArray))
			$this->statuses = VidiunSchedulerStatusArray::fromValuesArray($statusesArray, $this->id, $this->configuredId);
		
		$this->lastStatusStr = date('d-m-Y H:i:s', $this->lastStatus);
		
		return $this;
	}
	    
	/**
	 * @param Scheduler $dbData
	 * @return VidiunScheduler
	 */
	public function statusFromObject($dbData)
	{
		$this->fromObject($dbData);
		
		$this->workers = VidiunSchedulerWorkerArray::statusFromSchedulerWorkerArray($dbData->getWorkers());
		$this->configs = VidiunSchedulerConfigArray::fromDbArray($dbData->getConfigs());
		
		return $this;
	}

	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new Scheduler();
			
		if(!is_null($this->statuses) && $this->statuses instanceof VidiunSchedulerStatusArray)
			$dbData->setStatuses($this->statuses->toValuesArray());
			
		return parent::toObject($dbData, $props_to_skip);
	}
}