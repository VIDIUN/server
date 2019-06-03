<?php
/**
 * @package Scheduler
 * @subpackage Index
 */
class KIndexingKuserPermissionsEngine extends KIndexingEngine
{
	/* (non-PHPdoc)
	 * @see KIndexingEngine::index()
	 */
	protected function index(VidiunFilter $filter, $shouldUpdate) 
	{
		$this->indexPermissionsForUsers ($filter, $shouldUpdate);
	}

	protected function indexPermissionsForUsers (VidiunFilter $filter, $shouldUpdate)
	{
		$filter->orderBy = VidiunBaseEntryOrderBy::CREATED_AT_ASC;
		
		$usersList = VBatchBase::$vClient->user->listAction($filter, $this->pager);
		if(!$usersList->objects || !count($usersList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($usersList->objects as $user)
		{
			VBatchBase::$vClient->user->index($user->id, $shouldUpdate);
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