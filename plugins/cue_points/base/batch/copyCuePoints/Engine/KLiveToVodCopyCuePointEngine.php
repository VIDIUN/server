<?php
/**
 * @package plugins.cuePoints
 * @subpackage Scheduler
 */
class VLiveToVodCopyCuePointEngine extends VCopyCuePointEngine
{
    const MAX_CHUNK_DURATION_IN_SEC = 6;

    protected $currentSegmentStartTime = null;
    protected $currentSegmentEndTime = null;
    protected $amfData = null;
    
    public function copyCuePoints()
    {
        return $this->copyCuePointsToEntry($this->data->liveEntryId, $this->data->vodEntryId);
    }

    /**
     * @param VidiunCuePoint $cuePoint
     * @return bool
     */
    protected function shouldCopyCuePoint($cuePoint)
    {
        return true;
    }

    public function setData($data, $partnerId)
    {
        parent::setData($data, $partnerId);
        $amfArray = json_decode($data->amfArray);
        $this->currentSegmentStartTime = self::getSegmentStartTime($amfArray);
        $this->currentSegmentEndTime = self::getSegmentEndTime($amfArray, $data->lastSegmentDuration + $data->lastSegmentDrift) + self::MAX_CHUNK_DURATION_IN_SEC;
        self::normalizeAMFTimes($amfArray, $data->totalVodDuration, $data->lastSegmentDuration);
        $this->amfData = $amfArray;
    }

    protected function getOrderByField() {return 'createdAt';}

    public function validateJobData()
    {
        if (!$this->data || !($this->data instanceof VidiunLiveToVodJobData))
            return false;
        return parent::validateJobData();
    }

    public function getCuePointFilter($entryId, $status = CuePointStatus::READY)
    {
        $filter = parent::getCuePointFilter($entryId, $status);
        $filter->cuePointTypeIn = implode(",", array(self::CUE_POINT_CODE, self::CUE_POINT_THUMB, self::ANNOTATION));
        $filter->createdAtLessThanOrEqual = $this->currentSegmentEndTime;
        if($this->data->lastCuePointSyncTime)
            $filter->createdAtGreaterThanOrEqual = $this->data->lastCuePointSyncTime;
        return $filter;
    }

    public function calculateCuePointTimes($cuePoint)
    {
        // if the cp was before the segment start time - move it to the beginning of the segment.
        $cuePointCreationTime = max($cuePoint->createdAt * 1000, $this->currentSegmentStartTime * 1000);
        $cuePointDestStartTime = $this->getOffsetForTimestamp($cuePointCreationTime);

        $cuePointDestEndTime = null;
        if ($cuePoint->endTime)
            $cuePointDestEndTime = $this->getOffsetForTimestamp($cuePoint->endTime * 1000);
        return array($cuePointDestStartTime, $cuePointDestEndTime);
    }
    
    protected static function postProcessCuePoints($copiedCuePointIds)
    {
        VBatchBase::$vClient->startMultiRequest();
        foreach ($copiedCuePointIds as $copiedLiveCuePointId)
            VBatchBase::tryExecuteApiCall(array('VCopyCuePointEngine','cuePointUpdateStatus'), array($copiedLiveCuePointId, VidiunCuePointStatus::HANDLED));
        VBatchBase::$vClient->doMultiRequest();
    }

    protected static function getSegmentStartTime($amfArray)
    {
        if (count($amfArray) == 0)
        {
            VidiunLog::warning("getSegmentStartTime got an empty AMFs array - returning 0 as segment start time");
            return 0;
        }
        return ($amfArray[0]->ts - $amfArray[0]->pts) / 1000;
    }

    protected static function getSegmentEndTime($amfArray, $segmentDuration)
    {
        return ceil(((self::getSegmentStartTime($amfArray) * 1000) + $segmentDuration) / 1000);
    }
    // change the PTS of every amf to be relative to the beginning of the recording, and not to the beginning of the segment
    protected static function normalizeAMFTimes(&$amfArray, $totalVodDuration, $currentSegmentDuration)
    {
        foreach($amfArray as $key=>$amf)
            $amfArray[$key]->pts = $amfArray[$key]->pts  + $totalVodDuration - $currentSegmentDuration;
    }

    protected function getOffsetForTimestamp($timestamp, $overrideNegative = true)
    {
        $minDistanceAmf = $this->getClosestAMF($timestamp);
        $ret = 0;
        if (is_null($minDistanceAmf))
            VidiunLog::debug('minDistanceAmf is null - returning 0');
        elseif ($minDistanceAmf->ts > $timestamp)
            $ret = $minDistanceAmf->pts - ($minDistanceAmf->ts - $timestamp);
        else
            $ret = $minDistanceAmf->pts + ($timestamp - $minDistanceAmf->ts);
        // make sure we don't get a negative time
        if ($overrideNegative)
            $ret = max($ret,0);
        VidiunLog::debug('Returning offset of ' . $ret);
        return $ret;
    }

    protected function getClosestAMF($timestamp)
    {
        $amfArray = $this->amfData;
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
}
