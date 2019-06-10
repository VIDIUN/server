<?php
/**
 * @package plugins.cuePoints
 * @subpackage Scheduler
 */
class VMultiClipCopyCuePointEngine extends VCopyCuePointEngine
{
	/** @var VidiunClipDescription $currentClip */
	private $currentClip = null;

	private $cuePointTypes = array(self::CUE_POINT_THUMB, self::CUE_POINT_EVENT, self::ANNOTATION, self::CUE_POINT_AD, self::CUE_POINT_CODE);
	/**
	 * @return bool
	 * @throws Exception
	 */
	public function copyCuePoints()
	{
		$res = true;
		/** @var VidiunClipDescription $clipDescription */
		foreach ($this->data->clipsDescriptionArray as $clipDescription)
		{
			$this->currentClip = $clipDescription;
			$res &= $this->copyCuePointsToEntry($clipDescription->sourceEntryId, $this->data->destinationEntryId);
		}
		$this->mergeCuePoint($this->data->destinationEntryId);
		return $res;
	}

	/**
	 * @param VidiunCuePoint $cuePoint
	 * @return bool
	 */
	public function shouldCopyCuePoint($cuePoint)
	{
		if (parent::shouldCopyCuePoint($cuePoint))
		{
			return true;
		}
		$clipStartTime = $this->currentClip->startTime;
		$clipEndTime = $clipStartTime + $this->currentClip->duration;
		$calculatedEndTime = $cuePoint->calculatedEndTime;
		if ($cuePoint->isMomentary)
			$calculatedEndTime = $cuePoint->startTime;
		return is_null($calculatedEndTime) || TimeOffsetUtils::onTimeRange($cuePoint->startTime,$calculatedEndTime,$clipStartTime, $clipEndTime);
	}

	public function getCuePointFilter($entryId, $status = CuePointStatus::READY)
	{
		$filter = parent::getCuePointFilter($entryId, $status);
		$filter->cuePointTypeIn = implode(",", $this->cuePointTypes) . ',quiz.QUIZ_QUESTION';
		$filter->startTimeLessThanOrEqual = $this->currentClip->startTime + $this->currentClip->duration;
		return $filter;
	}

	/**
	 * @param $cuePoint
	 * @return array
	 */
	public function calculateCuePointTimes($cuePoint)
	{
		$clipStartTime = $this->currentClip->startTime;
		$offsetInDestination = $this->currentClip->offsetInDestination;
		$clipEndTime = $clipStartTime + $this->currentClip->duration;
		$cuePointDestStartTime = TimeOffsetUtils::getAdjustedStartTime($cuePoint->startTime, $clipStartTime, $offsetInDestination);
		$cuePointDestEndTime = TimeOffsetUtils::getAdjustedEndTime(self::getCalculatedEndTimeIfExist($cuePoint), $clipStartTime ,$clipEndTime ,$offsetInDestination);
		return array($cuePointDestStartTime, $cuePointDestEndTime);
	}

	public function validateJobData()
	{
		if (!$this->data || !($this->data instanceof VidiunMultiClipCopyCuePointsJobData))
			return false;
		if (is_null($this->data->clipsDescriptionArray))
			return false;
		return parent::validateJobData();
	}

	/**
	 * @param string $destinationEntryId
	 * @throws Exception
	 */
	private function mergeCuePoint($destinationEntryId)
	{
		$filter = $this->getCuePointFilterForMerge($destinationEntryId);
		$pager = $this->getCuePointPager();
		$cuePoints = $this->getAllCuePointFromNewEntry($filter, $pager);
		$this->mergeCuePointByType($cuePoints);
	}

	private function getCuePointFilterForMerge($destinationEntryId)
	{
		$filter = parent::getCuePointFilter($destinationEntryId);
		//merge annotation,Ad,event,thumb cue point only
		$filter->cuePointTypeIn = implode(",", $this->cuePointTypes);
		return $filter;
	}

	/**
	 * @param $cuePoints
	 * @return array
	 * @throws Exception
	 */
	private function mergeCuePointByType($cuePoints)
	{
		$cuePointSplitIntoType = array();
		foreach($this->cuePointTypes as $type)
		{
			$cuePointSplitIntoType[$type] = array();
		}
		/** @var VidiunCuePoint $cuePoint */
		foreach ($cuePoints as $cuePoint)
		{
			$this->handleNextCuePoint($cuePointSplitIntoType,$cuePoint);
		}
		return $cuePointSplitIntoType;
	}

	/**
	 * @param $filter
	 * @param $pager
	 * @return array
	 */
	private function getAllCuePointFromNewEntry($filter, $pager)
	{
		$cuePoints = array();
		do {
			$listResponse = VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine', 'cuePointList'), array($filter, $pager));
			if (!$listResponse)
				break;
			$cuePointsPage = $listResponse->objects;
			$pager->pageIndex++;
			$cuePoints = array_merge($cuePoints, $cuePointsPage);
		} while (count($cuePointsPage) == self::MAX_CUE_POINT_CHUNKS);
		return $cuePoints;
	}

	/**
	 * @param array $cuePointSplitIntoType
	 * @param VidiunCuePoint $cuePoint
	 */
	private function handleNextCuePoint(&$cuePointSplitIntoType, &$cuePoint)
	{
		/** @noinspection PhpIllegalArrayKeyTypeInspection */
		$type = &$cuePointSplitIntoType[$cuePoint->cuePointType];
		if (!key_exists($cuePoint->copiedFrom,$type))
			$type[$cuePoint->copiedFrom] = array();
		$copiedFromArray = $type[$cuePoint->copiedFrom];
		if ($copiedFromArray) // not Empty
		{
			$lastOfType = &$copiedFromArray[count($copiedFromArray)-1];
			if (!property_exists($lastOfType, 'endTime') || $lastOfType->endTime >= $cuePoint->startTime)
			{
				VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine', 'mergeConsecutiveCuePoint'),
					array($lastOfType, $cuePoint));
				if (property_exists($cuePoint, 'endTime'))
					$lastOfType->endTime = $cuePoint->endTime;
			}
			else //add new element to end of list
				$type[$cuePoint->copiedFrom][] = $cuePoint;
		}
		else // Empty(add new element)
			$type[$cuePoint->copiedFrom][] = $cuePoint;
	}

}
