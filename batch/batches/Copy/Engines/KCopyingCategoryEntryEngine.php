<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */
class VCopyingCategoryEntryEngine extends VCopyingEngine
{
	/* (non-PHPdoc)
	 * @see VCopyingEngine::copy()
	 */
	protected function copy(VidiunFilter $filter, VidiunObjectBase $templateObject) {
		return $this->copyCategoryEntries ($filter, $templateObject);
		
	}

	protected function copyCategoryEntries (VidiunFilter $filter, VidiunObjectBase $templateObject)
	{
		/* @var $filter VidiunCategoryEntryFilter */
		$filter->orderBy = VidiunCategoryEntryOrderBy::CREATED_AT_ASC;
		
		$categoryEntryList = VBatchBase::$vClient->categoryEntry->listAction($filter, $this->pager);
		if(!$categoryEntryList->objects || !count($categoryEntryList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryEntryList->objects as $categoryEntry)
		{
			$newCategoryEntry = $this->getNewObject($categoryEntry, $templateObject);
			VBatchBase::$vClient->categoryEntry->add($newCategoryEntry);
		}
		
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(is_array($result) && isset($result['code']))
				unset($results[$index]);
				
		if(!count($results))
			return 0;
			
		$lastCopyId = end($results);
		$this->setLastCopyId($lastCopyId);
		
		return count($results);
	}
	/* (non-PHPdoc)
	 * @see VCopyingEngine::getNewObject()
	 */
	protected function getNewObject(VidiunObjectBase $sourceObject, VidiunObjectBase $templateObject) {
		$class = get_class($sourceObject);
		$newObject = new $class();
		
		/* @var $newObject VidiunCategoryEntry */
		/* @var $sourceObject VidiunCategoryEntry */
		/* @var $templateObject VidiunCategoryEntry */
		
		$newObject->categoryId = $sourceObject->categoryId;
		$newObject->entryId = $sourceObject->entryId;
			
		if(!is_null($templateObject->categoryId))
			$newObject->categoryId = $templateObject->categoryId;
		if(!is_null($templateObject->entryId))
			$newObject->entryId = $templateObject->entryId;
	
		return $newObject;
	}	
}