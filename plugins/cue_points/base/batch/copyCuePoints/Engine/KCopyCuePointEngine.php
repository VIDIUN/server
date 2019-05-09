<?php
/**
 * @package plugins.cuePoints
 * @subpackage Scheduler
 */
abstract class VCopyCuePointEngine
{
	const MAX_CUE_POINT_CHUNKS = 500;

	const CUE_POINT_THUMB = 'thumbCuePoint.Thumb';
	const CUE_POINT_EVENT = 'eventCuePoint.Event';
	const ANNOTATION = 'annotation.Annotation';
	const CUE_POINT_AD = 'adCuePoint.Ad';
	const CUE_POINT_CODE = 'codeCuePoint.Code';

	protected $data = null;
	protected $partnerId = null;
	private $lastCuePointPerType = null;
	private $idsMap = array();

	abstract public function copyCuePoints();

	protected function shouldCopyCuePoint($cuePoint)
	{
		$extendedShouldCopyTagsArray = array(self::CUE_POINT_CODE => array("poll-data"));
		foreach($extendedShouldCopyTagsArray as $type => $tags)
		{
			if ($cuePoint->cuePointType == $type && count(array_intersect(explode(",", $cuePoint->tags), $tags)))
				return true;
		}
		return false;
	}

	protected function calculateCuePointTimes($cuePoint) {return array($cuePoint->startTime, $cuePoint->endTime);}

	protected function validateJobData() {return true;}

	protected function getOrderByField() {return 'startTime';}

	protected static function postProcessCuePoints($copiedCuePointIds) {}

	protected function preProcessCuePoints(&$cuePoints)
	{
		$this->setCalculatedEndTimeOnCuePoints($cuePoints);
	}

	protected function copyCuePointsToEntry($srcEntryId, $destEntryId)
	{
		$this->lastCuePointPerType  = array();
		$filter = $this->getCuePointFilter($srcEntryId);
		$filter->orderBy = '+startTime,+intId';
		$pager = $this->getCuePointPager();
		$clonedCuePointIds = array();
		do
		{
			VidiunLog::debug("Getting list of cue point for entry [$srcEntryId] with pager index: " . $pager->pageIndex);
			$listResponse = VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine','cuePointList'), array($filter, $pager));
			if (!$listResponse)
				return false;
			$cuePoints = $listResponse->objects;
			$this->preProcessCuePoints($cuePoints);
			VidiunLog::debug("Return " . count($cuePoints) . " cue-points from list");
			foreach ($cuePoints as &$cuePoint)
			{
				if ($this->shouldCopyCuePoint($cuePoint))
				{
					$clonedCuePointId = $this->copySingleCuePoint($cuePoint, $destEntryId);
					if ($clonedCuePointId)
					{
						$clonedCuePointIds[] = $clonedCuePointId;
					}
				}
			}
			$pager->pageIndex++;
		} while (count($cuePoints) == self::MAX_CUE_POINT_CHUNKS);
		$this->postProcessCuePoints($clonedCuePointIds);
		return true;
	}

	protected function copySingleCuePoint($cuePoint, $destEntryId)
	{
		$parentId = null;
		if (isset($cuePoint->parentId) && $cuePoint->parentId)
		{
			if (isset($this->idsMap[$cuePoint->parentId]))
			{
				$parentId = $this->idsMap[$cuePoint->parentId];
			}
			else
			{
				VidiunLog::warning("Cuepoint $cuePoint->parentId as parent of $cuePoint->id is not in ids map");
			}
		}

		$clonedCuePoint = VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine', 'cuePointClone'), array($cuePoint, $destEntryId, $parentId));
		if ($clonedCuePoint)
		{
			$this->idsMap[$cuePoint->id] = $clonedCuePoint->id;
			list($startTime, $endTime) = $this->calculateCuePointTimes($cuePoint);
			$res = VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine', 'updateCuePointTimes'), array($clonedCuePoint->id, $startTime, $endTime));
			if ($res)
				return $cuePoint->id;
			else
				VidiunLog::info("Update time for [{$cuePoint->id}] of [$startTime, $endTime] - Failed");
		}
		else
			VidiunLog::info("Could not copy [{$cuePoint->id}] - moving to next");
		return null;
	}


	public function setData($data, $partnerId)
	{
		$this->data = $data;
		$this->partnerId = $partnerId;
	}

	public static function initEngine($copyCuePointJobType, $data, $partnerId)
	{
		$engine = self::getEngine($copyCuePointJobType);
		if (!$engine)
			return null;
		$engine->setData($data, $partnerId);
		return $engine;
	}

	private static function getEngine($copyCuePointJobType) {
		switch($copyCuePointJobType)
		{
			case CopyCuePointJobType::MULTI_CLIP:
				return new VMultiClipCopyCuePointEngine();
			case CopyCuePointJobType::LIVE:
				return new VLiveToVodCopyCuePointEngine();
			case CopyCuePointJobType::LIVE_CLIPPING:
				return new VLiveClippingCopyCuePointEngine();
			default:
				return null;
		}
	}

	protected function getCuePointFilter($entryId, $status = CuePointStatus::READY)
	{
		$filter = new VidiunCuePointFilter();
		$filter->entryIdEqual = $entryId;
		$filter->statusIn = $status;
		$filter->orderBy = '+' . $this->getOrderByField();
		return $filter;
	}

	protected function getCuePointPager()
	{
		$pager = new VidiunFilterPager();
		$pager->pageIndex = 0;
		$pager->pageSize = self::MAX_CUE_POINT_CHUNKS;
		return $pager;
	}

	public static function updateCuePointTimes($cuePointId, $startTime, $endTime = null)
	{
		return VBatchBase::$vClient->cuePoint->updateCuePointsTimes($cuePointId, $startTime,$endTime);
	}

	public static function cuePointList($filter, $pager)
	{
		return VBatchBase::$vClient->cuePoint->listAction($filter, $pager);
	}

	public static function cuePointClone($cuePoint, $destinationEntryId, $parentId = null)
	{
		if ($cuePoint instanceof VidiunAnnotation)
		{
			return VBatchBase::$vClient->annotation->cloneAction($cuePoint->id, $destinationEntryId, $parentId);
		}
		else
		{
			return VBatchBase::$vClient->cuePoint->cloneAction($cuePoint->id, $destinationEntryId, $parentId);
		}
	}

	public static function cuePointUpdateStatus($cuePointId, $newStatus)
	{
		return VBatchBase::$vClient->cuePoint->updateStatus($cuePointId, $newStatus);
	}

	public static function deleteCuePoint($cuePointId)
	{
		return VBatchBase::$vClient->cuePoint->delete($cuePointId);
	}


	/**
	 * @param VidiunCuePoint $currentCuePoint
	 * @param VidiunCuePoint $nextCuePoint
	 * @return mixed
	 */
	public static function mergeConsecutiveCuePoint($currentCuePoint, $nextCuePoint)
	{
		VBatchBase::$vClient->startMultiRequest();
		if (property_exists($nextCuePoint,'endTime'))
			/** @noinspection PhpUndefinedFieldInspection */
			self::updateCuePointTimes($currentCuePoint->id,$currentCuePoint->startTime,$nextCuePoint->endTime);
		self::deleteCuePoint($nextCuePoint->id);
		return VBatchBase::$vClient->doMultiRequest();
	}


	protected function setCalculatedEndTimeOnCuePoints(&$cuePoints)
	{
		//setting calculatedEndTime on cue points in order to decide in shouldCopyCuePoint method
		$orderField = $this->getOrderByField();
		foreach ($cuePoints as &$cuePoint)
		{
			// set on calculatedEndTime the end time if existed or the next cue point start time
			$type = self::getTypeName($cuePoint);
			$cuePoint->calculatedEndTime = self::getEndTimeIfExist($cuePoint);
			/** we will only Override the calculated end time if cue point does not have end time of its own, if
			 * If cue Point has end time meaning it was set by user and we will continue with the times required by the user.
			 */
			if (array_key_exists($type, $this->lastCuePointPerType))
			{
				if (!$this->lastCuePointPerType[$type]->calculatedEndTime)
				{
					$this->lastCuePointPerType[$type]->calculatedEndTime = $cuePoint->$orderField;
				}
			}
			$this->lastCuePointPerType[$type] = &$cuePoint;
		}
	}

	private static function getEndTimeIfExist($cuePoint)
	{
		if (isset($cuePoint->endTime) && $cuePoint->endTime > 0)
		{
			return $cuePoint->endTime;
		}
		return null;
	}

	protected static function getCalculatedEndTimeIfExist($cuePoint)
	{
		if (isset($cuePoint->calculatedEndTime) && $cuePoint->calculatedEndTime > 0)
		{
			return $cuePoint->calculatedEndTime;
		}
		return self::getEndTimeIfExist($cuePoint);
	}

	private static function getTypeName($cuePoint) {
		$name = $cuePoint->cuePointType;
		if ($name == self::CUE_POINT_CODE && $cuePoint->tags == 'change-view-mode')
			$name .= '_changeViewMode';
		return $name;
	}

}
