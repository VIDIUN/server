<?php
/**
 * @package plugins.metadata
 * @subpackage Scheduler.Index
 */
class KIndexingMetadataEngine extends KIndexingEngine
{
	/**
	 * @param VidiunFilter $filter
	 * @param bool $shouldUpdate
	 * @return int
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate)
	{
		return $this->indexMetadataObjects($filter, $shouldUpdate);
	}

	/**
	 * @param VidiunMetadataFilter $filter
	 * @param $shouldUpdate
	 * @return int
	 */
	protected function indexMetadataObjects(VidiunMetadataFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunMetadataOrderBy::CREATED_AT_ASC;
		$metadataPlugin = VidiunMetadataClientPlugin::get(VBatchBase::$vClient);
		$metadataList = $metadataPlugin->metadata->listAction($filter, $this->pager);
		if(!count($metadataList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($metadataList->objects as $metadata)
		{
			$metadataPlugin->metadata->index($metadata->id, $shouldUpdate);
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
}
