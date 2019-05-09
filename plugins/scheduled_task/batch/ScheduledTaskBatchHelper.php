<?php

class ScheduledTaskBatchHelper
{
	/**
	 * @param VidiunClient $client
	 * @param VidiunScheduledTaskProfile $scheduledTaskProfile
	 * @param VidiunFilterPager $pager
	 * @param VidiunFilter $filter
	 * @return VidiunObjectListResponse
	 */
	public static function query(VidiunClient $client, VidiunScheduledTaskProfile $scheduledTaskProfile, VidiunFilterPager $pager, $filter = null)
	{
		$objectFilterEngineType = $scheduledTaskProfile->objectFilterEngineType;
		$objectFilterEngine = VObjectFilterEngineFactory::getInstanceByType($objectFilterEngineType, $client);
		$objectFilterEngine->setPageSize($pager->pageSize);
		$objectFilterEngine->setPageIndex($pager->pageIndex);
		if(!$filter)
			$filter = $scheduledTaskProfile->objectFilter;

		return $objectFilterEngine->query($filter);
	}

	/**
	 * @param VidiunBaseEntryArray $entries
	 * @param $createAtTime
	 * @return array
	 */
	public static function getEntriesIdWithSameCreateAtTime($entries, $createAtTime)
	{
		$result = array();
		foreach ($entries as $entry)
		{
			if($entry->createdAt == $createAtTime)
				$result[] = $entry->id;
		}

		return $result;
	}

	/**
	 * @param  VidiunMediaType $mediaType
	 * @return string
	 */
	public static function getMediaTypeString($mediaType)
	{
		$relectionClass =  new ReflectionClass ('VidiunMediaType');
		$mapping = $relectionClass->getConstants();
		return array_search($mediaType, $mapping);
	}
}