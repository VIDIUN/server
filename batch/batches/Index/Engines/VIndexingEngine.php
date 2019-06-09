<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
abstract class KIndexingEngine
{
	/**
	 * @var VidiunFilterPager
	 */
	protected $pager;
	
	/**
	 * @var int
	 */
	private $lastIndexId;

	/**
	 * @var int
	 */
	private $lastIndexDepth;
	
	/**
	 * The partner that owns the objects
	 * @var int
	 */
	private $partnerId;
	
	/**
	 * The batch system partner id
	 * @var int
	 */
	private $batchPartnerId;
	
	/**
	 * @param int $objectType of enum VidiunIndexObjectType
	 * @return KIndexingEngine
	 */
	public static function getInstance($objectType)
	{
		switch($objectType)
		{
			case VidiunIndexObjectType::ENTRY:
				return new KIndexingEntryEngine();
				
			case VidiunIndexObjectType::CATEGORY:
				return new KIndexingCategoryEngine();
				
			case VidiunIndexObjectType::LOCK_CATEGORY:
				return new KIndexingCategoryEngine();
				
			case VidiunIndexObjectType::CATEGORY_ENTRY:
				return new KIndexingCategoryEntryEngine();
				
			case VidiunIndexObjectType::CATEGORY_USER:
				return new KIndexingCategoryUserEngine();
				
			case VidiunIndexObjectType::USER:
				return new KIndexingKuserPermissionsEngine();
				
			default:
				return VidiunPluginManager::loadObject('KIndexingEngine', $objectType);
		}
	}
	
	/**
	 * @param int $partnerId
	 */
	public function configure($partnerId)
	{
		$this->partnerId = $partnerId;
		$this->batchPartnerId = VBatchBase::$taskConfig->getPartnerId();

		$this->pager = new VidiunFilterPager();
		$this->pager->pageSize = 100;

		if(VBatchBase::$taskConfig->params && VBatchBase::$taskConfig->params->pageSize)
			$this->pager->pageSize = VBatchBase::$taskConfig->params->pageSize;
	}
	
	/**
	 * @param VidiunFilter $filter The filter should return the list of objects that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the object columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed objects
	 */
	public function run(VidiunFilter $filter, $shouldUpdate)
	{
		VBatchBase::impersonate($this->partnerId);
		$ret = $this->index($filter, $shouldUpdate);
		VBatchBase::unimpersonate();
		
		return $ret;
	}
	
	/**
	 * @param VidiunFilter $filter The filter should return the list of objects that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the object columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed objects
	 */
	abstract protected function index(VidiunFilter $filter, $shouldUpdate);
	
	/**
	 * @return int $lastIndexId
	 */
	public function getLastIndexId()
	{
		return $this->lastIndexId;
	}

	/**
	 * @param int $lastIndexId
	 */
	protected function setLastIndexId($lastIndexId)
	{
		$this->lastIndexId = $lastIndexId;
	}

	/**
	 * @return int $lastIndexDepth
	 */
	public function getLastIndexDepth()
	{
		return $this->lastIndexDepth;
	}

	/**
	 * @param int $lastIndexDepth
	 */
	protected function setLastIndexDepth($lastIndexDepth)
	{
		$this->lastIndexDepth = $lastIndexDepth;
	}

	public function initAdvancedFilter($data, $advancedFilter = null)
	{
		if(!$advancedFilter)
			$advancedFilter = new VidiunIndexAdvancedFilter();
		
		if($data->lastIndexId)
			$advancedFilter->indexIdGreaterThan = $data->lastIndexId;
		if($data->lastIndexDepth)
			$advancedFilter->depthGreaterThanEqual = $data->lastIndexDepth;
		
		return $advancedFilter;
	}	
}
