<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveEntryServerNodeRecordingInfoArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunLiveEntryServerNodeRecordingInfoArray();
		if ($arr == null)
			return $newArr;
	
		foreach ($arr as $obj)
		{
			$nObj = new VidiunLiveEntryServerNodeRecordingInfo();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
	
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunLiveEntryServerNodeRecordingInfo");
	}
}