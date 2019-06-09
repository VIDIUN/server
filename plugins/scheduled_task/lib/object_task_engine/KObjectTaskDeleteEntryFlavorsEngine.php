<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskDeleteEntryFlavorsEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunDeleteEntryFlavorsObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		$deleteType = $objectTask->deleteType;
		$flavorParamsIds = explode(',', $objectTask->flavorParamsIds);
		$client = $this->getClient();

		$pager = new VidiunFilterPager();
		$pager->pageSize = 500; // use max size, throw exception in case we got more than 500 flavors where pagination is not supported
		$filter = new VidiunFlavorAssetFilter();
		$filter->entryIdEqual = $object->id;
		try
		{
			$flavorsResponse = $client->flavorAsset->listAction($filter);
		}
		catch(Exception $ex)
		{
			throw $ex;
		}
		if ($flavorsResponse->totalCount > $pager->pageSize)
			throw new Exception('Too many flavors were found where pagination is not supported');

		$flavors = $flavorsResponse->objects;
		VidiunLog::info('Found '.count($flavors). ' flavors');
		if (!count($flavors))
			return;

		VidiunLog::info('Delete type is '.$deleteType);
		switch($deleteType)
		{
			case VidiunDeleteFlavorsLogicType::DELETE_LIST:
				$this->deleteFlavorByList($flavors, $flavorParamsIds);
				break;
			case VidiunDeleteFlavorsLogicType::KEEP_LIST_DELETE_OTHERS:
				$this->deleteFlavorsKeepingConfiguredList($flavors, $flavorParamsIds);
				break;
			case VidiunDeleteFlavorsLogicType::DELETE_KEEP_SMALLEST:
				$this->deleteAllButKeepSmallest($flavors);
				break;
		}
	}

	/**
	 * @param $id
	 */
	protected function deleteFlavor($id, $partnerId)
	{
		$client = $this->getClient();
		try
		{
			$client->flavorAsset->delete($id);
			VidiunLog::info('Flavor id '.$id.' was deleted');
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex);
			VidiunLog::err('Failed to delete flavor id '.$id);
		}
	}

	protected function findSmallestFlavor($flavors)
	{
		/** @var VidiunFlavorAsset $smallestFlavor */
		$smallestFlavor = null;
		foreach($flavors as $flavor)
		{
			/** @var VidiunFlavorAsset $flavor */
			if ($flavor->status != VidiunFlavorAssetStatus::READY)
				continue;

			if (!$flavor->size) // flavor must have size
				continue;

			if (is_null($smallestFlavor) || $flavor->size < $smallestFlavor->size)
			{
				$smallestFlavor = $flavor;
			}
		}

		return $smallestFlavor;
	}

	/**
	 * @param $flavors
	 * @param $flavorParamsIds
	 */
	protected function deleteFlavorsKeepingConfiguredList(array $flavors, array $flavorParamsIds)
	{
		// make sure at least one flavor will be left from the configured list
		$atLeastOneFlavorWillBeLeft = false;
		foreach ($flavors as $flavor)
		{
			/** @var $flavor VidiunFlavorAsset */
			if ($flavor->status != VidiunFlavorAssetStatus::READY)
				continue;

			if (in_array($flavor->flavorParamsId, $flavorParamsIds))
			{
				$atLeastOneFlavorWillBeLeft = true;
				break;
			}
		}

		if (!$atLeastOneFlavorWillBeLeft)
		{
			VidiunLog::warning('No flavors will be left after deletion, cannot continue.');
			return;
		}

		foreach ($flavors as $flavor)
		{
			/** @var $flavor VidiunFlavorAsset */
			if (!in_array($flavor->flavorParamsId, $flavorParamsIds))
			{
				$this->deleteFlavor($flavor->id, $flavor->partnerId);
			}
		}
	}

	/**
	 * @param $flavors
	 * @param $flavorParams
	 */
	protected function deleteFlavorByList(array $flavors, array $flavorParams)
	{
		foreach ($flavors as $flavor)
		{
			/** @var $flavor VidiunFlavorAsset */
			if (in_array($flavor->flavorParamsId, $flavorParams))
			{
				$this->deleteFlavor($flavor->id, $flavor->partnerId);
			}
		}
	}

	protected function deleteAllButKeepSmallest(array $flavors)
	{
		$smallestFlavor = $this->findSmallestFlavor($flavors);
		if (is_null($smallestFlavor))
		{
			VidiunLog::warning('Smallest flavor was not found, cannot continue');
			return;
		}
		$this->deleteFlavorsKeepingConfiguredList($flavors, array($smallestFlavor->flavorParamsId));
	}
}