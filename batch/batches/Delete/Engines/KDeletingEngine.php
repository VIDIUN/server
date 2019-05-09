<?php
/**
 * @package Scheduler
 * @subpackage Delete
 */
abstract class VDeletingEngine
{
	/**
	 * @var VidiunClient
	 */
	protected $client;
	
	/**
	 * @var VidiunFilterPager
	 */
	protected $pager;
	
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
	 * @param int $objectType of enum VidiunDeleteObjectType
	 * @return VDeletingEngine
	 */
	public static function getInstance($objectType)
	{
		switch($objectType)
		{
			case VidiunDeleteObjectType::CATEGORY_ENTRY:
				return new VDeletingCategoryEntryEngine();
				
			case VidiunDeleteObjectType::CATEGORY_USER:
				return new VDeletingCategoryUserEngine();

			case VidiunDeleteObjectType::GROUP_USER:
				return new VDeletingGroupUserEngine();

			case VidiunDeleteObjectType::CATEGORY_ENTRY_AGGREGATION:
 				return new VDeletingAggregationChannelEngine();
				
			case VidiunDeleteObjectType::USER_ENTRY :
 				return new VDeletingUserEntryEngine();
			
			default:
				return VidiunPluginManager::loadObject('VDeletingEngine', $objectType);
		}
	}
	
	/**
	 * @param int $partnerId
	 * @param VidiunDeleteJobData $jobData
  	 * @param VidiunClient $client
  	 */
	public function configure($partnerId, $jobData)
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
	public function run(VidiunFilter $filter)
	{
		VBatchBase::impersonate($this->partnerId);
		$ret = $this->delete($filter);
		VBatchBase::unimpersonate();
		
		return $ret;
	}
	
	/**
	 * @param VidiunFilter $filter The filter should return the list of objects that need to be deleted
	 * @return int the number of deleted objects
	 */
	abstract protected function delete(VidiunFilter $filter);
}