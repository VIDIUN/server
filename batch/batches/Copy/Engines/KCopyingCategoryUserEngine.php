<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */
class VCopyingCategoryUserEngine extends VCopyingEngine
{
	/* (non-PHPdoc)
	 * @see VCopyingEngine::copy()
	 */
	protected function copy(VidiunFilter $filter, VidiunObjectBase $templateObject)
	{
		return $this->copyCategoryUsers($filter, $templateObject);
	}
	
	/**
	 * @param VidiunCategoryUserFilter $filter The filter should return the list of category users that need to be copied
	 * @param VidiunCategoryUser $templateObject Template object to overwrite attributes on the copied object
	 * @return int the number of copied category users
	 */
	protected function copyCategoryUsers(VidiunCategoryUserFilter $filter, VidiunCategoryUser $templateObject)
	{
		$filter->orderBy = VidiunCategoryUserOrderBy::CREATED_AT_ASC;
		
		$categoryUsersList = VBatchBase::$vClient->categoryUser->listAction($filter, $this->pager);
		if(!$categoryUsersList->objects || !count($categoryUsersList->objects))
			return 0;
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($categoryUsersList->objects as $categoryUser)
		{
			$newCategoryUser = $this->getNewObject($categoryUser, $templateObject);
			VBatchBase::$vClient->categoryUser->add($newCategoryUser);
		}
		
		$results = VBatchBase::$vClient->doMultiRequest();
		foreach($results as $index => $result)
			if(!is_int($result))
				unset($results[$index]);
				
		if(!count($results))
			return 0;
			
		$lastCopyId = end($results);
		$this->setLastCopyId($lastCopyId);
		
		return count($results);
	}
	
	/**
	 * @see VCopyingEngine::getNewObject()
	 * 
	 * @param VidiunCategoryUser $sourceObject
	 * @param VidiunCategoryUser $templateObject
	 * @return VidiunCategoryUser
	 */
	protected function getNewObject(VidiunObjectBase $sourceObject, VidiunObjectBase $templateObject)
	{
		$class = get_class($sourceObject);
		$newObject = new $class();
		
		/* @var $newObject VidiunCategoryUser */
		/* @var $sourceObject VidiunCategoryUser */
		/* @var $templateObject VidiunCategoryUser */
		
		$newObject->categoryId = $sourceObject->categoryId;
		$newObject->userId = $sourceObject->userId;
		$newObject->permissionLevel = $sourceObject->permissionLevel;
		$newObject->updateMethod = $sourceObject->updateMethod;
			
		if(!is_null($templateObject->categoryId))
			$newObject->categoryId = $templateObject->categoryId;
		if(!is_null($templateObject->userId))
			$newObject->userId = $templateObject->userId;
		if(!is_null($templateObject->permissionLevel))
			$newObject->permissionLevel = $templateObject->permissionLevel;
		if(!is_null($templateObject->updateMethod))
			$newObject->updateMethod = $templateObject->updateMethod;
	
		return $newObject;
	}
}
