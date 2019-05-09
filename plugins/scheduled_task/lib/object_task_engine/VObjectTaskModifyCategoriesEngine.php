<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskModifyCategoriesEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunModifyCategoriesObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		if (is_null($objectTask))
			return;

		$entryId = $object->id;
		$addRemoveType = $objectTask->addRemoveType;
		$taskCategoryIds = array();
		if (!is_array($objectTask->categoryIds))
			$objectTask->categoryIds = array();
		foreach($objectTask->categoryIds as $categoryIntValue)
		{
			/** @var VidiunString $categoryIntValue */
			$taskCategoryIds[] = $categoryIntValue->value;
		}

		if ($addRemoveType == VidiunScheduledTaskAddOrRemoveType::MOVE)
		{
			$this->removeAllCategories($entryId, $object->partnerId);
			$addRemoveType = VidiunScheduledTaskAddOrRemoveType::ADD;
		}

		// remove all categories if nothing was configured in the list
		if (count($taskCategoryIds) == 0 && $addRemoveType == VidiunScheduledTaskAddOrRemoveType::REMOVE)
		{
			$this->removeAllCategories($entryId, $object->partnerId);
		}
		else
		{
			foreach($taskCategoryIds as $categoryId)
			{
				try
				{
					$this->processCategory($entryId, $categoryId, $addRemoveType);
				}
				catch(Exception $ex)
				{
					VidiunLog::err($ex);
				}
			}
		}
	}

	/**
	 * @param $entryId
	 * @param $categoryId
	 * @param $addRemoveType
	 */
	public function processCategory($entryId, $categoryId, $addRemoveType)
	{
		$client = $this->getClient();
		$categoryEntry = null;
		$filter = new VidiunCategoryEntryFilter();
		$filter->entryIdEqual = $entryId;
		$filter->categoryIdEqual = $categoryId;
		$categoryEntryListResponse = $client->categoryEntry->listAction($filter);
		/** @var VidiunCategoryEntry $categoryEntry */
		if (count($categoryEntryListResponse->objects))
			$categoryEntry = $categoryEntryListResponse->objects[0];

		if (is_null($categoryEntry) && $addRemoveType == VidiunScheduledTaskAddOrRemoveType::ADD)
		{
			$categoryEntry = new VidiunCategoryEntry();
			$categoryEntry->entryId = $entryId;
			$categoryEntry->categoryId = $categoryId;
			$client->categoryEntry->add($categoryEntry);
		}
		elseif (!is_null($categoryEntry) && $addRemoveType == VidiunScheduledTaskAddOrRemoveType::REMOVE)
		{
			$client->categoryEntry->delete($entryId, $categoryId);
		}
	}

	/**
	 * @param $entryId
	 * @param $partnerId
	 */
	public function removeAllCategories($entryId, $partnerId)
	{
		try
		{
			$this->doRemoveAllCategories($entryId);
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex);
		}
	}

	/**
	 * @param $entryId
	 */
	public function doRemoveAllCategories($entryId)
	{
		$client = $this->getClient();
		$filter = new VidiunCategoryEntryFilter();
		$filter->entryIdEqual = $entryId;
		$categoryEntryListResponse = $client->categoryEntry->listAction($filter);
		foreach($categoryEntryListResponse->objects as $categoryEntry)
		{
			/** @var $categoryEntry VidiunCategoryEntry */
			$client->categoryEntry->delete($entryId, $categoryEntry->categoryId);
		}
	}
}