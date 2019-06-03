<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */

/**
 * Will copy objects and add them
 * according to the suppolied engine type and filter 
 *
 * @package Scheduler
 * @subpackage Copy
 */
class VAsyncLiveToVod extends VJobHandlerWorker
{
	const MAX_CUE_POINTS_TO_COPY_TO_VOD = 100;
	const MAX_CHUNK_DURATION_IN_SEC = 12;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::LIVE_TO_VOD;
	}
	/**
	 * (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	protected function getJobType()
	{
		return VidiunBatchJobType::LIVE_TO_VOD;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->copyCuePoint($job, $job->data);
	}
	
	/**
	 * Will take a data and copy cue points
	 */
	private function copyCuePoint(VidiunBatchJob $job, VidiunLiveToVodJobData $data)
	{
		$amfArray = json_decode($data->amfArray);
		$currentSegmentStartTime = self::getSegmentStartTime($amfArray);
		$currentSegmentEndTime = self::getSegmentEndTime($amfArray, $data->lastSegmentDuration + $data->lastSegmentDrift) + self::MAX_CHUNK_DURATION_IN_SEC;
		self::normalizeAMFTimes($amfArray, $data->totalVodDuration, $data->lastSegmentDuration);

		$totalCount = self::getCuePointCount($data->liveEntryId, $currentSegmentEndTime, $data->lastCuePointSyncTime);
		if ($totalCount == 0)
			return $this->closeJob($job, null, null, "No cue point to copy", VidiunBatchJobStatus::FINISHED);
		else
			VidiunLog::info("Total count of cue-point to copy: " .$totalCount);
		
		do
		{
			$copiedCuePointIds = array();
			$liveCuePointsToCopy = self::getCuePointListForEntry($data->liveEntryId, $currentSegmentEndTime, $data->lastCuePointSyncTime);
			if (count($liveCuePointsToCopy) == 0)
				break;

			//set the parnter ID for adding the new cue points in multi request
			VBatchBase::impersonate($liveCuePointsToCopy[0]->partnerId);
			VBatchBase::$vClient->startMultiRequest();
			foreach ($liveCuePointsToCopy as $liveCuePoint)
			{
				$copiedCuePointId = self::copyCuePointToVOD($liveCuePoint, $currentSegmentStartTime, $amfArray, $data->vodEntryId);
				if ($copiedCuePointId)
					$copiedCuePointIds[] = $copiedCuePointId;
				else
					VidiunLog::info("Not copying cue point [$liveCuePoint->id]");
			}
			$response = VBatchBase::$vClient->doMultiRequest();
			self::checkForErrorInMultiRequestResponse($response);
			VBatchBase::unimpersonate();
			
			//start post-process for all copied cue-point
			VidiunLog::info("Copied [".count($copiedCuePointIds)."] cue-points");
			self::postProcessCuePoints($copiedCuePointIds);

			//decrease the totalCount (as the number of cue point return from server)
			$totalCount -= count($liveCuePointsToCopy);
		} while ($totalCount);

		return $this->closeJob($job, null, null, "Copy all cue points finished", VidiunBatchJobStatus::FINISHED);
	}


	private static function checkForErrorInMultiRequestResponse($response)
	{
		foreach ($response as $item)
			if (VBatchBase::$vClient->isError($item))  //throwExceptionIfError
				VidiunLog::alert("Error in copy");
	}

	private static function postProcessCuePoints($copiedCuePointIds)
	{
		VBatchBase::$vClient->startMultiRequest();
		foreach ($copiedCuePointIds as $copiedLiveCuePointId)
			VBatchBase::$vClient->cuePoint->updateStatus($copiedLiveCuePointId, VidiunCuePointStatus::HANDLED);
		VBatchBase::$vClient->doMultiRequest();
	}

	private static function getCuePointFilter($entryId, $currentSegmentEndTime, $lastCuePointSyncTime = null)
	{
		$filter = new VidiunCuePointFilter();
		$filter->entryIdEqual = $entryId;
		$filter->statusIn = CuePointStatus::READY;
		$filter->cuePointTypeIn = 'codeCuePoint.Code,thumbCuePoint.Thumb,annotation.Annotation';
		$filter->createdAtLessThanOrEqual = $currentSegmentEndTime;
		if($lastCuePointSyncTime)
			$filter->createdAtGreaterThanOrEqual = $lastCuePointSyncTime;
		return $filter;
	}

	private static function getCuePointCount($entryId, $currentSegmentEndTime, $lastCuePointSyncTime)
	{
		$filter = self::getCuePointFilter($entryId, $currentSegmentEndTime, $lastCuePointSyncTime);
		return VBatchBase::$vClient->cuePoint->count($filter);
	}

	private static function getCuePointListForEntry($entryId, $currentSegmentEndTime, $lastCuePointSyncTime)
	{
		$filter = self::getCuePointFilter($entryId, $currentSegmentEndTime, $lastCuePointSyncTime);
		$pager = new VidiunFilterPager();
		$pager->pageSize = self::MAX_CUE_POINTS_TO_COPY_TO_VOD;
		$maxRetries = 3;
		$exception = null;
		do
		{
			try
			{
				$result = VBatchBase::$vClient->cuePoint->listAction($filter, $pager);
				return $result->objects;
			}
			catch (Exception $ex)
			{
				$maxRetries--;
				$exception = $ex;
			}
		}
		while($maxRetries);

		throw $exception;
	}

	private static function getSegmentStartTime($amfArray)
	{
		if (count($amfArray) == 0)
		{
			VidiunLog::warning("getSegmentStartTime got an empty AMFs array - returning 0 as segment start time");
			return 0;
		}
		return ($amfArray[0]->ts - $amfArray[0]->pts) / 1000;
	}

	private static function getSegmentEndTime($amfArray, $segmentDuration)
	{
		return ceil(((self::getSegmentStartTime($amfArray) * 1000) + $segmentDuration) / 1000);
	}
	// change the PTS of every amf to be relative to the beginning of the recording, and not to the beginning of the segment
	private static function normalizeAMFTimes(&$amfArray, $totalVodDuration, $currentSegmentDuration)
	{
		foreach($amfArray as $key=>$amf)
			$amfArray[$key]->pts = $amfArray[$key]->pts  + $totalVodDuration - $currentSegmentDuration;
	}

	private static function getOffsetForTimestamp($timestamp, $amfArray)
	{
		$minDistanceAmf = self::getClosestAMF($timestamp, $amfArray);
		$ret = 0;
		if (is_null($minDistanceAmf))
			VidiunLog::debug('minDistanceAmf is null - returning 0');
		elseif ($minDistanceAmf->ts > $timestamp)
			$ret = $minDistanceAmf->pts - ($minDistanceAmf->ts - $timestamp);
		else
			$ret = $minDistanceAmf->pts + ($timestamp - $minDistanceAmf->ts);
		// make sure we don't get a negative time
		$ret = max($ret,0);
		VidiunLog::debug('AMFs array is:' . print_r($amfArray, true) . 'getOffsetForTimestamp returning ' . $ret);
		return $ret;
	}

	private static function getClosestAMF($timestamp, $amfArray)
	{
		$len = count($amfArray);
		$ret = null;
		if ($len == 1)
			$ret = $amfArray[0];
		else if ($timestamp >= $amfArray[$len-1]->ts)
			$ret = $amfArray[$len-1];
		else if ($timestamp <= $amfArray[0]->ts)
			$ret = $amfArray[0];
		else if ($len > 1)
		{
			$lo = 0;
			$hi = $len - 1;
			while ($hi - $lo > 1)
			{
				$mid = round(($lo + $hi) / 2);
				if ($amfArray[$mid]->ts <= $timestamp)
					$lo = $mid;
				else
					$hi = $mid;
			}
			if (abs($amfArray[$hi]->ts - $timestamp) > abs($amfArray[$lo]->ts - $timestamp))
				$ret = $amfArray[$lo];
			else
				$ret = $amfArray[$hi];
		}
		VidiunLog::debug('getClosestAMF returning ' . print_r($ret, true));
		return $ret;
	}


	private static function cuePointFactory($cuePointType) {
		switch($cuePointType) {
			case "codeCuePoint.Code":
				return new VidiunCodeCuePoint();
			case "thumbCuePoint.Thumb":
				return new VidiunThumbCuePoint();
			case "annotation.Annotation":
				return new VidiunAnnotation();
			default:
				return null;
		}
	}

	private static function createCuePointToUpdate($cuePointType, $startTime){
		$newCuePoint = self::cuePointFactory($cuePointType);
		if (!$newCuePoint)
			return null;
		$newCuePoint->startTime = $startTime;
		return $newCuePoint;

	}
	private static function copyCuePointToVOD($liveCuePoint, $currentSegmentStartTime, $amfArray, $vodEntryId)
	{
		$cuePointCreationTime = $liveCuePoint->createdAt * 1000;
		// if the cp was before the segment start time - move it to the beginning of the segment.
		$cuePointCreationTime = max($cuePointCreationTime, $currentSegmentStartTime * 1000);

		$startTimeForCuePoint = self::getOffsetForTimestamp($cuePointCreationTime, $amfArray);
		if (!is_null($startTimeForCuePoint)) {
			$VODCuePoint = VBatchBase::$vClient->cuePoint->cloneAction($liveCuePoint->id, $vodEntryId);
			if (VBatchBase::$vClient->isError($VODCuePoint))
				return null;

			$cuePointToUpdate = self::createCuePointToUpdate($liveCuePoint->cuePointType, $startTimeForCuePoint);
			if ($cuePointToUpdate) {
				$res = VBatchBase::$vClient->cuePoint->update($VODCuePoint->id, $cuePointToUpdate);
				if (VBatchBase::$vClient->isError($res))
					return null;
				else return $liveCuePoint->id;
			}
		}
		return null;
	}
}
