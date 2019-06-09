<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
class KIndexingEntryEngine extends KIndexingEngine
{
	/* (non-PHPdoc)
	 * @see KIndexingEngine::index()
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate)
	{
		return $this->indexEntries($filter, $shouldUpdate);
	}
	
	/**
	 * @param VidiunBaseEntryFilter $filter The filter should return the list of entries that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the entry columns and attributes values should be recalculated before reindexed
	 * @return int the number of indexed entries
	 */
	protected function indexEntries(VidiunBaseEntryFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunBaseEntryOrderBy::CREATED_AT_ASC;
		
		$entriesList = VBatchBase::$vClient->baseEntry->listAction($filter, $this->pager);
		if(!$entriesList->objects || !count($entriesList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($entriesList->objects as $entry)
		{
			VBatchBase::$vClient->baseEntry->index($entry->id, $shouldUpdate);
		}
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(!is_int($result))
				unset($results[$index]);
				
		if(!count($results))
			return 0;
			
		$lastIndexId = end($results);
		$this->setLastIndexId($lastIndexId);
		
		return count($results);
	}
	
	public function initAdvancedFilter($data, $advancedFilter = null)
	{
		if(!$advancedFilter)
			$advancedFilter = new VidiunEntryIndexAdvancedFilter();
		
		return parent::initAdvancedFilter($data, $advancedFilter);
	}
}
