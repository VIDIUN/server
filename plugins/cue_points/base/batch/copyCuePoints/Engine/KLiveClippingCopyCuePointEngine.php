<?php
/**
 * @package plugins.cuePoints
 * @subpackage Scheduler
 */
class VLiveClippingCopyCuePointEngine extends VLiveToVodCopyCuePointEngine
{
    //override set status to HANDLED as LiveToVod engine
    protected static function postProcessCuePoints($copiedCuePointIds) {}

    protected function shouldCopyCuePoint($cuePoint)
    {
        if (VCopyCuePointEngine::shouldCopyCuePoint($cuePoint))
        {
            return true;
        }
        $cuePointStartTime = $this->getOffsetForTimestamp($cuePoint->createdAt * 1000, false);
        $cuePointEndTime = $this->getOffsetForTimestamp($cuePoint->calculatedEndTime * 1000, false);
        VidiunLog::debug("Checking times to know if copy is needed for id[". $cuePoint->id ."]: start- [$cuePointStartTime], end- [$cuePointEndTime], calculatedEndTime - " . $cuePoint->calculatedEndTime);
        if ($cuePointStartTime < 0 && is_null($cuePoint->calculatedEndTime))
        {
            return $this->shouldCopyCuePointBeforeTimeWindow($cuePoint); //if cue point started before the clip but end afterward (no next cue point)
        }
        return ($cuePointStartTime >= 0 || $cuePointEndTime > 0);
    }

    public function getCuePointFilter($entryId, $status = CuePointStatus::READY)
    {
        $statuses = array(CuePointStatus::READY, CuePointStatus::HANDLED);
        $filter = parent::getCuePointFilter($entryId, implode(",",$statuses));
        return $filter;
    }
    
    /**
     * @param VidiunCuePoint $cuePoint
     * @return boolean
     */
    private function shouldCopyCuePointBeforeTimeWindow($cuePoint)
    {
        if ($cuePoint instanceof VidiunCodeCuePoint)
        {
            $noCopiedTag = array("poll-data","select-poll-state","poll-results");
            return (count(array_intersect(explode(",", $cuePoint->tags), $noCopiedTag)) == 0);
        }
        return true;
    }
}
