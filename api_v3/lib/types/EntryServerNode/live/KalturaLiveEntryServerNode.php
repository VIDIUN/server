<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveEntryServerNode extends VidiunEntryServerNode 
{
	const MAX_BITRATE_PERCENTAGE_DIFF_ALLOWED = 10;
	const MAX_FRAMERATE_PERCENTAGE_DIFF_ALLOWED = 15;
	
	/**
	 * parameters of the stream we got
	 * @var VidiunLiveStreamParamsArray
	 */
	public $streams;

	/**
	 * @var VidiunLiveEntryServerNodeRecordingInfoArray
	 */
	public $recordingInfo;

	/**
	 * @var bool
	 * @requiresPermission read,insert,update
	 */
	public $isPlayableUser;

	private static $map_between_objects = array
	(
		"streams",
		"recordingInfo",
		"isPlayableUser",
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			$object_to_fill = new LiveEntryServerNode();
		}
		return parent::toObject($object_to_fill, $props_to_skip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$dbStreamsInfo = $sourceObject ? $sourceObject->getStreams() : array();
		$inputStreamsInfo = isset($this->streams) ? $this->streams : new VidiunLiveStreamParamsArray();
		
		if(count($dbStreamsInfo) === count($inputStreamsInfo))
		{
			$this->clearInputStreamInfoIfoNotChanged($dbStreamsInfo, $inputStreamsInfo->toObjectsArray());
		}
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	private function clearInputStreamInfoIfoNotChanged($dbStreams, $inputStreamInfo)
	{
		$clearInputStreamInfo = true;
		$dbStreamsInfo = $this->buildStreamInfoKeyValueArray($dbStreams);
		$inputStreamsInfo = $this->buildStreamInfoKeyValueArray($inputStreamInfo);
		
		foreach ($inputStreamsInfo as $flavorId => $flavorInfo)
		{
			$dbStreamInfo = $dbStreamsInfo[$flavorId] ? $dbStreamsInfo[$flavorId] : null;
			/* @var $dbStreamInfo vLiveStreamParams */
			/* @var $flavorInfo vLiveStreamParams */
			if(!$dbStreamInfo)
			{
				$clearInputStreamInfo = false;
				break;
			}
		
			$bitratePrecentageDiff = $this->getPercentageDiff($flavorInfo->getBitrate(), $dbStreamInfo->getBitrate());
			$frameRatePrecentageDiff = $this->getPercentageDiff($flavorInfo->getFrameRate(), $dbStreamInfo->getFrameRate());
			if($bitratePrecentageDiff > self::MAX_BITRATE_PERCENTAGE_DIFF_ALLOWED || $frameRatePrecentageDiff > self::MAX_FRAMERATE_PERCENTAGE_DIFF_ALLOWED)
			{
				$clearInputStreamInfo = false;
				break;
			}
		}
		
		if($clearInputStreamInfo)
			$this->streams = null;
	}
	
	private function getPercentageDiff($newValue, $oldValue)
	{
		$percentChange = (1 - $oldValue/$newValue) * 100;
		return abs(round($percentChange, 0));
	}
	
	private function buildStreamInfoKeyValueArray($streamInfo = array())
	{
		$result = array();
		foreach($streamInfo as $info)
		{
			/* @var $info vLiveStreamParams */
			$result[$info->getFlavorId()] = $info;
		}
		
		return $result;
	}
}
