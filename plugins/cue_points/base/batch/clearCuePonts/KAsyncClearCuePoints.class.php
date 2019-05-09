<?php
/**
 * @package Scheduler
 * @subpackage ClearCuePoints
 */

/**
 * Clear cue points from live entries that were not marked as handled (cases were recording is off)
 *
 * @package Scheduler
 * @subpackage ClearCuePoints
 */
class VAsyncClearCuePoints extends VPeriodicWorker
{	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CLEANUP;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$entryFilter = new VidiunLiveStreamEntryFilter();
		$entryFilter->isLive = VidiunNullableBoolean::TRUE_VALUE;
		$entryFilter->orderBy = VidiunLiveStreamEntryOrderBy::CREATED_AT_ASC;
		
		$entryFilter->moderationStatusIn = 
			VidiunEntryModerationStatus::PENDING_MODERATION . ',' .
			VidiunEntryModerationStatus::APPROVED . ',' .
			VidiunEntryModerationStatus::REJECTED . ',' .
			VidiunEntryModerationStatus::FLAGGED_FOR_REVIEW . ',' .
			VidiunEntryModerationStatus::AUTO_APPROVED;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 100;
		$pager->pageIndex = 1;
		
		$entries = self::$vClient->liveStream->listAction($entryFilter, $pager);
		
		while(count($entries->objects))
		{
			foreach($entries->objects as $entry)
			{
				//When entry has recording on the cue poitns are copied from the live entry to the vod entry
				//The copy process allready markes the live entry cue points as handled
				/* @var $entry VidiunLiveEntry */
				if($entry->recordStatus !== VidiunRecordStatus::DISABLED)
					continue;
					
				$this->clearEntryCuePoints($entry);
			}
			
			$pager->pageIndex++;
			$entries = self::$vClient->liveStream->listAction($entryFilter, $pager);
		}
	}
	
	private function clearEntryCuePoints($entry)
	{
		$cuePointPlugin = VidiunCuePointClientPlugin::get(self::$vClient);
		
		$cuePointFilter = $this->getAdvancedFilter("VidiunCuePointFilter");
		$cuePointFilter->entryIdEqual = $entry->id;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 100;
		
		$cuePoints = $cuePointPlugin->cuePoint->listAction($cuePointFilter, $pager);

		if(!$cuePoints->objects)
		{
			VidiunLog::debug("No cue points found for entry [{$entry->id}] continue to next live entry");
			return;
		}

		//Clear Max 100 cue points each run on each live entry to avoid massive old cue points updates
		self::impersonate($entry->partnerId);
		self::$vClient->startMultiRequest();
		foreach ($cuePoints->objects as $cuePoint)
		{
			$cuePointPlugin->cuePoint->updateStatus($cuePoint->id, VidiunCuePointStatus::HANDLED);
		}
		self::$vClient->doMultiRequest();
		self::unimpersonate();
	}
}
